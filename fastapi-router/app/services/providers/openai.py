import logging
from openai import AsyncOpenAI
from app.core.config import settings

logger = logging.getLogger(__name__)
client = AsyncOpenAI(api_key=settings.openai_api_key)

async def call_openai(prompt: str) -> dict:
    try:
        response = await client.chat.completions.create(
            model="gpt-4o",
            messages=[{"role": "user", "content": prompt}],
            max_tokens=1000,
        )
        return {
            "response": response.choices[0].message.content,
            "model":    "gpt-4o",
            "provider": "openai",
        }
    except Exception as e:
        logger.error(f"OpenAI error: {e}")
        raise