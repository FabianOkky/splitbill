"""OCR engine abstraction.

Keeps the HTTP contract in `main.py` stable while letting us swap the
underlying engine. Today: Tesseract 5 + `ind` via pytesseract. Tomorrow:
RapidOCR (ONNX). The contract is purely "bytes in -> raw_text out".
"""

from __future__ import annotations

import io
import os
from typing import Protocol

from PIL import Image, ImageOps

import pytesseract


class OcrEngine(Protocol):
    """Anything that turns image bytes into raw OCR text."""

    name: str

    def recognize(self, image_bytes: bytes) -> str:  # pragma: no cover - protocol
        ...


class TesseractEngine:
    """Tesseract 5 with the Indonesian language pack.

    Footprint ~10 MB, ~1s/page on weak CPU. Good for clean printed struk.
    For photographed/skewed struk, swap to RapidOCR (see rule 03).
    """

    name = "tesseract"

    def __init__(self, lang: str = "ind") -> None:
        self.lang = lang

    def recognize(self, image_bytes: bytes) -> str:
        with Image.open(io.BytesIO(image_bytes)) as img:
            # Auto-rotate using EXIF, then convert to RGB for consistency.
            img = ImageOps.exif_transpose(img)
            if img.mode != "RGB":
                img = img.convert("RGB")
            return pytesseract.image_to_string(img, lang=self.lang)


def build_engine() -> OcrEngine:
    """Build the engine declared in OCR_ENGINE env (default: tesseract).

    Adding a new engine = add an `elif` and ship the import inside the
    branch so optional deps stay optional.
    """
    name = os.environ.get("OCR_ENGINE", "tesseract").strip().lower()

    if name == "tesseract":
        return TesseractEngine(lang=os.environ.get("OCR_LANG", "ind"))

    # Placeholder for the planned upgrade. Keeping it here documents the
    # swap point without dragging the dep into requirements.txt yet.
    if name == "rapidocr":  # pragma: no cover - not installed by default
        raise RuntimeError(
            "rapidocr engine not installed; pip install rapidocr-onnxruntime "
            "and wire a RapidOcrEngine class in engine.py"
        )

    raise RuntimeError(f"Unknown OCR_ENGINE: {name!r}")
