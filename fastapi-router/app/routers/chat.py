from fastapi import APIRouter
from app.models.schemas import RouteRequest, RouteResponse
from app.services.analyzer import analyze_intent
from app.services.selector import select_model_and_call
import time

router = APIRouter()

@router.post("/route", response_model=RouteResponse)
async def route_prompt(request: RouteRequest):
    start = time.time()

    intent      = await analyze_intent(request.prompt)
    result      = await select_model_and_call(request.prompt, intent)
    elapsed_ms  = int((time.time() - start) * 1000)

    return RouteResponse(
        response         = result["response"],
        model_used       = result["model"],
        provider         = result["provider"],
        detected_intent  = intent,
        response_time_ms = elapsed_ms,
    )