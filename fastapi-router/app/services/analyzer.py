import json
import logging
from openai import AsyncOpenAI
from app.models.schemas import IntentType
from app.core.config import settings

logger = logging.getLogger(__name__)

client = AsyncOpenAI(api_key=settings.openai_api_key)

SYSTEM_PROMPT = """
You are an intent classifier. Given a user prompt, 
your job is to detect the primary intent and return 
ONLY a JSON object with this exact format:

{
  "intent": "<intent>",
  "confidence": <float between 0 and 1>
}

The intent must be one of:
- "code"    → writing, debugging, explaining code
- "image"   → generating, editing, describing images
- "text"    → emails, summaries, articles, translation
- "general" → everything else

No explanation. No markdown. JSON only.
"""

async def analyze_intent(prompt: str) -> IntentType:
    try:
        response = await client.chat.completions.create(
            model="gpt-4o-mini",
            messages=[
                {"role": "system", "content": SYSTEM_PROMPT},
                {"role": "user",   "content": prompt},
            ],
            temperature=0,
            max_tokens=60,
        )

        raw = response.choices[0].message.content.strip()
        data = json.loads(raw)
        intent = data.get("intent", "general")

        logger.info(f"Intent detected: {intent} "
                    f"(confidence: {data.get('confidence')})")

        return IntentType(intent)

    except (json.JSONDecodeError, ValueError) as e:
        logger.warning(f"Intent parsing failed: {e}. Falling back to general.")
        return IntentType.GENERAL

    except Exception as e:
        logger.error(f"Analyzer error: {e}. Falling back to general.")
        return IntentType.GENERAL