"""One-shot: convert fotos/*.HEIC|*.jpeg → assets/images/fotos/{full,thumb}/*.{jpg,webp}.

Full = max 1920px long edge, Thumb = max 800px long edge.
EXIF orientation is applied, metadata stripped, saved as sRGB.
"""
from __future__ import annotations
import sys
from pathlib import Path

from PIL import Image, ImageOps
import pillow_heif

pillow_heif.register_heif_opener()

SRC = Path(__file__).resolve().parent
OUT = SRC.parent / "images" / "fotos"
FULL = OUT / "full"
THUMB = OUT / "thumb"
FULL.mkdir(parents=True, exist_ok=True)
THUMB.mkdir(parents=True, exist_ok=True)

FULL_MAX = 1920
THUMB_MAX = 800
JPEG_Q = 85
WEBP_Q = 82

sources = sorted(
    p for p in SRC.iterdir()
    if p.is_file() and p.suffix.lower() in {".heic", ".heif", ".jpg", ".jpeg", ".png"}
)
if not sources:
    print("no sources found", file=sys.stderr)
    sys.exit(1)

for src in sources:
    stem = src.stem.lower()
    print(f"→ {src.name}")
    with Image.open(src) as im:
        im = ImageOps.exif_transpose(im).convert("RGB")

        for max_edge, dest_dir in ((FULL_MAX, FULL), (THUMB_MAX, THUMB)):
            resized = im.copy()
            resized.thumbnail((max_edge, max_edge), Image.Resampling.LANCZOS)
            jpg = dest_dir / f"{stem}.jpg"
            webp = dest_dir / f"{stem}.webp"
            resized.save(jpg, "JPEG", quality=JPEG_Q, optimize=True, progressive=True)
            resized.save(webp, "WEBP", quality=WEBP_Q, method=6)
            print(f"   {jpg.relative_to(SRC.parent.parent)}  {jpg.stat().st_size//1024} KB")
            print(f"   {webp.relative_to(SRC.parent.parent)}  {webp.stat().st_size//1024} KB")

print("done.")
