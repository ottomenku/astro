import json
import sys
from datetime import datetime, timezone

import swisseph as swe


PLANETS = [
    (swe.SUN, "Sun"),
    (swe.MOON, "Moon"),
    (swe.MERCURY, "Mercury"),
    (swe.VENUS, "Venus"),
    (swe.MARS, "Mars"),
    (swe.JUPITER, "Jupiter"),
    (swe.SATURN, "Saturn"),
    (swe.URANUS, "Uranus"),
    (swe.NEPTUNE, "Neptune"),
    (swe.PLUTO, "Pluto"),
    (swe.TRUE_NODE, "True Node"),
]

SIGNS = [
    "Aries",
    "Taurus",
    "Gemini",
    "Cancer",
    "Leo",
    "Virgo",
    "Libra",
    "Scorpio",
    "Sagittarius",
    "Capricorn",
    "Aquarius",
    "Pisces",
]


def parse_datetime_utc(value: str) -> datetime:
    dt = datetime.fromisoformat(value.replace("Z", "+00:00"))
    if dt.tzinfo is None:
        dt = dt.replace(tzinfo=timezone.utc)
    return dt.astimezone(timezone.utc)


def julian_day(dt: datetime) -> float:
    return swe.julday(dt.year, dt.month, dt.day, dt.hour + dt.minute / 60 + dt.second / 3600)


def normalize_degree(deg: float) -> float:
    return deg % 360.0


def zodiac_sign(deg: float) -> tuple[str, float]:
    deg = normalize_degree(deg)
    sign_index = int(deg // 30)
    sign_deg = deg % 30
    return SIGNS[sign_index], sign_deg


def calc_ayanamsa(jd: float, sidereal: bool) -> float:
    if not sidereal:
        return 0.0
    return swe.get_ayanamsa(jd)


def calc_asc_mc(jd: float, lat: float, lon: float, ayanamsa: float) -> dict:
    cusps, ascmc = swe.houses(jd, lat, lon)
    asc = normalize_degree(ascmc[0] - ayanamsa)
    mc = normalize_degree(ascmc[1] - ayanamsa)
    return {"asc": asc, "mc": mc, "cusps": cusps}


def calc_house_cusps(jd: float, lat: float, lon: float, ayanamsa: float, house_system: str) -> list[float]:
    if house_system == "placidus":
        cusps, _ascmc = swe.houses(jd, lat, lon, b"P")
        # Swiss ephemeris cuspok 1..12 indexeléssel jönnek; Python tuple 0..11
        # Sziderikus esetén ugyanazt az ayanamsa eltolást alkalmazzuk a cuspokra.
        return [normalize_degree(c - ayanamsa) for c in cusps]

    # whole_sign
    return whole_sign_houses(calc_asc_mc(jd, lat, lon, ayanamsa)["asc"])


def whole_sign_houses(asc: float) -> list[float]:
    first_sign_index = int(normalize_degree(asc) // 30)
    houses = []
    for i in range(12):
        houses.append(normalize_degree((first_sign_index + i) * 30))
    return houses


def calc_planets(jd: float, ayanamsa: float) -> list[dict]:
    results = []
    flags = swe.FLG_SWIEPH | swe.FLG_MOSEPH
    for planet_id, name in PLANETS:
        lon, lat, dist = swe.calc_ut(jd, planet_id, flags)[0][:3]
        lon = normalize_degree(lon - ayanamsa)
        sign_name, sign_deg = zodiac_sign(lon)
        results.append(
            {
                "name": name,
                "longitude": lon,
                "sign": sign_name,
                "sign_degree": sign_deg,
            }
        )
    return results


def format_house(asc: float, longitude: float) -> int:
    diff = normalize_degree(longitude - asc)
    return int(diff // 30) + 1


def build_chart(entry: dict, sidereal: bool, house_system: str) -> dict:
    dt = parse_datetime_utc(entry["datetime_utc"])
    lat = float(entry["lat"])
    lon = float(entry["lon"])
    jd = julian_day(dt)
    ayanamsa = calc_ayanamsa(jd, sidereal)
    asc_mc = calc_asc_mc(jd, lat, lon, ayanamsa)
    houses = calc_house_cusps(jd, lat, lon, ayanamsa, house_system)
    planets = calc_planets(jd, ayanamsa)
    for planet in planets:
        # ház számítása: whole_sign esetén ASC alapján, placidusnál cuspok alapján
        if house_system == "placidus":
            # megkeressük, melyik cusp-szegmensbe esik a bolygó
            # cusp lista: 12 elem, 1. ház csúcs = houses[0]
            lon_p = planet["longitude"]
            house_num = 12
            for i in range(12):
                start = houses[i]
                end = houses[(i + 1) % 12]
                # szegmens wrap kezeléssel
                if start <= end:
                    in_seg = start <= lon_p < end
                else:
                    in_seg = lon_p >= start or lon_p < end
                if in_seg:
                    house_num = i + 1
                    break
            planet["house"] = house_num
        else:
            planet["house"] = format_house(asc_mc["asc"], planet["longitude"])
    return {
        "datetime_utc": dt.isoformat(),
        "lat": lat,
        "lon": lon,
        "ayanamsa": ayanamsa,
        "asc": asc_mc["asc"],
        "mc": asc_mc["mc"],
        "houses": houses,
        "planets": planets,
    }


def main() -> None:
    raw = sys.stdin.read()
    payload = json.loads(raw)

    sidereal = bool(payload.get("sidereal", False))
    ayanamsa_name = payload.get("ayanamsa", "lahiri")
    house_system = payload.get("house_system", "whole_sign")

    if house_system not in ("whole_sign", "placidus"):
        house_system = "whole_sign"

    if sidereal:
        # jelenleg csak Lahiri támogatott
        if ayanamsa_name == "lahiri":
            swe.set_sid_mode(swe.SIDM_LAHIRI)
        else:
            swe.set_sid_mode(swe.SIDM_LAHIRI)

    natal = build_chart(payload["natal"], sidereal, house_system)
    transit = build_chart(payload["transit"], sidereal, house_system)

    output = {
        "sidereal": sidereal,
        "ayanamsa": ayanamsa_name if sidereal else None,
        "house_system": house_system,
        "natal": natal,
        "transit": transit,
    }

    sys.stdout.write(json.dumps(output))


if __name__ == "__main__":
    main()