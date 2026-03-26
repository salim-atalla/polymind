from fastapi import APIRouter, HTTPException
from app.models.schemas import RouteRequest, RouteResponse
from app.services.analyzer import analyze_intent
from app.services.selector import select_model_and_call
import time

router = APIRouter()

@router.post(
    "/route",
    response_model=RouteResponse,
    summary="Route a prompt to the best AI model",
    description="""
Analyzes the intent of the prompt and automatically routes it
to the most suitable AI model.

**Intent detection:**
- `code` → Claude (Anthropic)
- `text` → GPT-4o (OpenAI)
- `image` → Gemini 1.5 Pro (Google)
- `general` → GPT-4o (OpenAI)

If the primary model fails, fallback models are tried automatically.
    """,
    responses={
        200: {"description": "Successful response from the selected AI model"},
        500: {"description": "All AI providers failed"},
    },
)
async def route_prompt(request: RouteRequest):
    start = time.time()

    try:
        intent     = await analyze_intent(request.prompt)
        result     = await select_model_and_call(request.prompt, intent)
        elapsed_ms = int((time.time() - start) * 1000)

        return RouteResponse(
            response         = result["response"],
            model_used       = result["model"],
            provider         = result["provider"],
            detected_intent  = intent,
            response_time_ms = elapsed_ms,
        )

    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"All providers failed: {str(e)}"
        )