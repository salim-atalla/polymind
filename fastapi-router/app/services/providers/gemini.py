import logging
import google.generativeai as genai
from app.core.config import settings

logger = logging.getLogger(__name__)
genai.configure(api_key=settings.gemini_api_key)

async def call_gemini(prompt: str) -> dict:
    try:
        model    = genai.GenerativeModel("gemini-1.5-pro")
        response = await model.generate_content_async(prompt)
        return {
            "response": response.text,
            "model":    "gemini-1.5-pro",
            "provider": "google",
        }
    except Exception as e:
        logger.error(f"Gemini error: {e}")
        raise