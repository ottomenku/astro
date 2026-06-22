import json
import sys
from datetime import datetime, timedelta, timezone

import swisseph as swe


PLANET_IDS = {
    "Sun": swe.SUN,
    "Moon": swe.MOON,
    "Mercury": swe.MERCURY,
    "Venus": swe.VENUS,
    "Mars": swe.MARS,
    "Jupiter": swe.JUPITER,
    "Saturn": swe.SATURN,
    "Uranus": swe.URANUS,
    "Neptune": swe.NEPTUNE,
    "Pluto": swe.PLUTO,
    "True Node": swe.TRUE_NODE,
}


def parse_datetime_utc(value: str) -> datetime:
    dt = datetime.fromisoformat(value.replace("Z", "+00:00"))
    if dt.tzinfo is None:
        dt = dt.replace(tzinfo=timezone.utc)
    return dt.astimezone(timezone.utc)


def julian_day(dt: datetime) -> float:
    return swe.julday(dt.year, dt.month, dt.day, dt.hour + dt.minute / 60 + dt.second / 3600)


def normalize_degree(deg: float) -> float:
    return deg % 360.0


def angle_delta(a: float, b: float) -> float:
    d = abs(normalize_degree(a) - normalize_degree(b))
    return min(d, 360.0 - d)


def calc_ayanamsa(jd: float, sidereal: bool) -> float:
    if not sidereal:
        return 0.0
    return swe.get_ayanamsa(jd)


def calc_house_cusps(jd: float, lat: float, lon: float, ayanamsa: float, house_system: str) -> list[float]:
    if house_system == "placidus":
        cusps, _ascmc = swe.houses(jd, lat, lon, b"P")
        return [normalize_degree(c - ayanamsa) for c in cusps]

    # whole sign
    cusps, ascmc = swe.houses(jd, lat, lon)
    asc = normalize_degree(ascmc[0] - ayanamsa)
    first_sign_index = int(normalize_degree(asc) // 30)
    return [normalize_degree((first_sign_index + i) * 30) for i in range(12)]


def calc_planet_longitude(jd: float, planet_name: str, ayanamsa: float) -> float:
    pid = PLANET_IDS.get(planet_name)
    if pid is None:
        raise ValueError(f"Unknown planet: {planet_name}")
    flags = swe.FLG_SWIEPH | swe.FLG_MOSEPH
    lon = swe.calc_ut(jd, pid, flags)[0][0]
    return normalize_degree(lon - ayanamsa)


def house_of_longitude(houses: list[float], lon_p: float) -> int:
    # houses: 12 cusp (1..12) degrees
    lon_p = normalize_degree(lon_p)
    house_num = 12
    for i in range(12):
        start = houses[i]
        end = houses[(i + 1) % 12]
        if start <= end:
            in_seg = start <= lon_p < end
        else:
            in_seg = lon_p >= start or lon_p < end
        if in_seg:
            house_num = i + 1
            break
    return house_num


def predicate_enter_house(dt: datetime, lat: float, lon: float, sidereal: bool, house_system: str, planet: str, target_house: int) -> tuple[bool, dict]:
    jd = julian_day(dt)
    ay = calc_ayanamsa(jd, sidereal)
    houses = calc_house_cusps(jd, lat, lon, ay, house_system)
    lon_p = calc_planet_longitude(jd, planet, ay)
    house = house_of_longitude(houses, lon_p)
    ok = house == target_house
    return ok, {"longitude": lon_p, "house": house}


def predicate_aspect(dt: datetime, sidereal: bool, planet: str, natal_longitude: float, aspect_angle: float, orb: float) -> tuple[bool, dict]:
    jd = julian_day(dt)
    ay = calc_ayanamsa(jd, sidereal)
    lon_p = calc_planet_longitude(jd, planet, ay)
    d = angle_delta(lon_p, natal_longitude)
    ok = abs(d - aspect_angle) <= orb
    return ok, {"longitude": lon_p, "delta": d, "orb": abs(d - aspect_angle)}


def refine_first_true(start: datetime, end: datetime, pred_fn, max_iters: int = 40) -> tuple[datetime, dict]:
    # Bináris keresés: az első TRUE pillanatot közelítjük.
    lo = start
    hi = end
    last_meta = {}
    for _ in range(max_iters):
        if (hi - lo).total_seconds() <= 60:
            ok, meta = pred_fn(hi)
            last_meta = meta
            return hi, last_meta
        mid = lo + (hi - lo) / 2
        ok, meta = pred_fn(mid)
        if ok:
            hi = mid
            last_meta = meta
        else:
            lo = mid
    ok, meta = pred_fn(hi)
    return hi, meta


def main() -> None:
    raw = sys.stdin.read()
    payload = json.loads(raw)

    start = parse_datetime_utc(payload["datetime_start_utc"])
    end = parse_datetime_utc(payload["datetime_end_utc"])
    if end <= start:
        raise ValueError("End must be after start")

    lat = float(payload["lat"])
    lon = float(payload["lon"])
    sidereal = bool(payload.get("sidereal", False))
    ayanamsa_name = payload.get("ayanamsa", "lahiri")
    house_system = payload.get("house_system", "placidus")
    step_hours = float(payload.get("step_hours", 6))

    if sidereal:
        if ayanamsa_name == "lahiri":
            swe.set_sid_mode(swe.SIDM_LAHIRI)
        else:
            swe.set_sid_mode(swe.SIDM_LAHIRI)

    event = payload["event"]
    etype = event["type"]

    def pred(dt: datetime):
        if etype == "enter_house":
            return predicate_enter_house(
                dt,
                lat,
                lon,
                sidereal,
                house_system,
                event["planet"],
                int(event["house"]),
            )
        if etype == "aspect_to_natal":
            return predicate_aspect(
                dt,
                sidereal,
                event["planet"],
                float(event["natal_longitude"]),
                float(event["aspect_angle"]),
                float(event.get("orb", 1.0)),
            )
        raise ValueError(f"Unsupported event.type: {etype}")

    ok0, meta0 = pred(start)
    if ok0:
        sys.stdout.write(
            json.dumps(
                {
                    "found": True,
                    "datetime_utc": start.isoformat(),
                    "meta": meta0,
                }
            )
        )
        return

    t_prev = start
    ok_prev = ok0
    meta_prev = meta0

    step = timedelta(hours=step_hours)
    t = start
    found = None
    found_meta = None
    while t < end:
        t = min(t + step, end)
        ok, meta = pred(t)
        if (not ok_prev) and ok:
            # átmenet t_prev -> t között
            def pred_only(x: datetime):
                return pred(x)

            refined_dt, refined_meta = refine_first_true(t_prev, t, lambda x: pred_only(x))
            found = refined_dt
            found_meta = refined_meta
            break
        ok_prev = ok
        meta_prev = meta
        t_prev = t

    sys.stdout.write(
        json.dumps(
            {
                "found": found is not None,
                "datetime_utc": found.isoformat() if found else None,
                "meta": found_meta if found_meta else None,
            }
        )
    )


if __name__ == "__main__":
    main()
