import logging
from app.models.schemas import IntentType
import app.services.providers.openai as openai_provider
import app.services.providers.claude as claude_provider
import app.services.providers.gemini as gemini_provider

logger = logging.getLogger(__name__)

ROUTING_TABLE = {
    IntentType.CODE:    "claude",
    IntentType.TEXT:    "openai",
    IntentType.IMAGE:   "gemini",
    IntentType.GENERAL: "openai",
}

PROVIDERS = {
    "openai": openai_provider,
    "claude": claude_provider,
    "gemini": gemini_provider,
}

FALLBACK_ORDER = ["openai", "claude", "gemini"]

async def _call_provider(name: str, prompt: str) -> dict:
    provider = PROVIDERS[name]
    fn = getattr(provider, f"call_{name}")
    return await fn(prompt)

async def select_model_and_call(prompt: str, intent: IntentType) -> dict:
    primary_name = ROUTING_TABLE.get(intent, "openai")

    try:
        logger.info(f"Routing intent '{intent}' to {primary_name}")
        return await _call_provider(primary_name, prompt)

    except Exception as primary_error:
        logger.warning(f"Primary '{primary_name}' failed: {primary_error}. Trying fallbacks...")

        for fallback_name in FALLBACK_ORDER:
            if fallback_name == primary_name:
                continue
            try:
                logger.info(f"Trying fallback: {fallback_name}")
                return await _call_provider(fallback_name, prompt)
            except Exception as e:
                logger.warning(f"Fallback '{fallback_name}' failed: {e}")

        raise Exception("All providers failed.")