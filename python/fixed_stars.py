import os

import swisseph as swe

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

# Fontosabb fix csillagok (tradicionális horoszkópia)
IMPORTANT_STARS = [
    {"id": "Regulus", "symbol": "R"},
    {"id": "Aldebaran", "symbol": "A"},
    {"id": "Antares", "symbol": "H"},
    {"id": "Fomalhaut", "symbol": "F"},
    {"id": "Algol", "symbol": "G"},
    {"id": "Sirius", "symbol": "S"},
    {"id": "Spica", "symbol": "V"},
    {"id": "Vega", "symbol": "L"},
    {"id": "Betelgeuse", "symbol": "B"},
    {"id": "Rigel", "symbol": "g"},
    {"id": "Altair", "symbol": None},
    {"id": "Alcyone", "symbol": None},
    {"id": "Capella", "symbol": "C"},
    {"id": "Arcturus", "symbol": None},
    {"id": "Pollux", "symbol": "P"},
    {"id": "Procyon", "symbol": None},
    {"id": "Deneb", "symbol": None},
    {"id": "Castor", "symbol": None},
]


def normalize_degree(deg: float) -> float:
    return deg % 360.0


def zodiac_sign(deg: float) -> tuple[str, float]:
    deg = normalize_degree(deg)
    sign_index = int(deg // 30)
    sign_deg = deg % 30
    return SIGNS[sign_index], sign_deg


def calc_fixed_stars(jd: float, ayanamsa: float) -> list[dict]:
    script_dir = os.path.dirname(os.path.abspath(__file__))
    if not os.path.isfile(os.path.join(script_dir, "ephe", "sefstars.txt")):
        return []

    flags = swe.FLG_SWIEPH | swe.FLG_MOSEPH
    results: list[dict] = []
    previous_dir = os.getcwd()

    try:
        os.chdir(script_dir)
        swe.set_ephe_path("ephe")

        for star in IMPORTANT_STARS:
            try:
                xx, _stnam, _retflags = swe.fixstar2(star["id"], jd, flags)
                lon = normalize_degree(xx[0] - ayanamsa)
                sign_name, sign_deg = zodiac_sign(lon)
                results.append(
                    {
                        "id": star["id"],
                        "name": star["id"],
                        "symbol": star["symbol"],
                        "longitude": lon,
                        "sign": sign_name,
                        "sign_degree": sign_deg,
                    }
                )
            except Exception:
                continue
    finally:
        os.chdir(previous_dir)

    return results
