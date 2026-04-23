import json
import os
import sys
from pathlib import Path


def extract_lines(result: object) -> list[dict[str, object]]:
    lines: list[dict[str, object]] = []

    for page in result or []:
        if hasattr(page, "get"):
            texts = page.get("rec_texts") or []
            scores = page.get("rec_scores") or []

            for index, raw_text in enumerate(texts):
                text = str(raw_text or "").strip()
                if not text:
                    continue
                confidence = scores[index] if index < len(scores) else None
                lines.append({
                    "text": text,
                    "confidence": confidence,
                })

            if lines:
                continue

        for item in page or []:
            if not item or len(item) < 2:
                continue
            text_info = item[1] or []
            text = ""
            confidence = None
            if len(text_info) >= 1:
                text = str(text_info[0] or "").strip()
            if len(text_info) >= 2:
                confidence = text_info[1]
            if text:
                lines.append({
                    "text": text,
                    "confidence": confidence,
                })

    return lines


def main() -> int:
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Image path is required."}))
        return 1

    image_path = Path(sys.argv[1]).expanduser().resolve()
    lang = (sys.argv[2] if len(sys.argv) > 2 else "en").strip() or "en"

    if not image_path.is_file():
        print(json.dumps({"error": f"Image file not found: {image_path}"}))
        return 1

    try:
        from paddleocr import PaddleOCR
    except Exception as exc:
        print(json.dumps({"error": f"PaddleOCR import failed: {exc}"}))
        return 2

    try:
        # Avoid the extra document preprocessing models for faster, leaner OCR.
        os.environ.setdefault("PADDLE_PDX_DISABLE_MODEL_SOURCE_CHECK", "True")
        ocr = PaddleOCR(
            lang=lang,
            use_doc_orientation_classify=False,
            use_doc_unwarping=False,
            use_textline_orientation=False,
        )
        result = ocr.predict(str(image_path))
    except Exception as exc:
        print(json.dumps({"error": f"PaddleOCR run failed: {exc}"}))
        return 3

    lines = extract_lines(result)

    raw_text = "\n".join(line["text"] for line in lines if line["text"].strip())
    print(json.dumps({
        "rawText": raw_text,
        "lines": lines,
    }, ensure_ascii=False))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
