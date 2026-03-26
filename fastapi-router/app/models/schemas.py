from pydantic import BaseModel, ConfigDict
from enum import Enum

class IntentType(str, Enum):
    TEXT    = "text"
    CODE    = "code"
    IMAGE   = "image"
    GENERAL = "general"

class RouteRequest(BaseModel):
    prompt: str
    user_id: str
    conversation_id: str | None = None

class RouteResponse(BaseModel):
    model_config = ConfigDict(protected_namespaces=())

    response:         str
    model_used:       str
    provider:         str
    detected_intent:  IntentType
    response_time_ms: int