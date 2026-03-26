import logging
import anthropic
from app.core.config import settings

logger = logging.getLogger(__name__)
client = anthropic.AsyncAnthropic(api_key=settings.anthropic_api_key)

async def call_claude(prompt: str) -> dict:
    try:
        response = await client.messages.create(
            model="claude-opus-4-6",
            max_tokens=1000,
            messages=[{"role": "user", "content": prompt}],
        )
        return {
            "response": response.content[0].text,
            "model":    "claude-opus-4-6",
            "provider": "anthropic",
        }
    except Exception as e:
        logger.error(f"Claude error: {e}")
        raise