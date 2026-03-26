from pydantic import BaseModel, ConfigDict, Field
from enum import Enum

class IntentType(str, Enum):
    TEXT    = "text"
    CODE    = "code"
    IMAGE   = "image"
    GENERAL = "general"

class RouteRequest(BaseModel):
    prompt: str = Field(
        ...,
        description="The user's prompt to be routed to the best AI model",
        examples=["Write a Python function to reverse a string"],
        min_length=1,
        max_length=10000,
    )
    user_id: str = Field(
        ...,
        description="The ID of the user sending the prompt",
        examples=["user_abc123"],
    )
    conversation_id: str | None = Field(
        default=None,
        description="Optional conversation ID for context tracking",
        examples=["conv_xyz789"],
    )

    model_config = ConfigDict(
        json_schema_extra={
            "examples": [
                {
                    "prompt": "Write a Python function to reverse a string",
                    "user_id": "user_abc123",
                    "conversation_id": "conv_xyz789",
                }
            ]
        }
    )

class RouteResponse(BaseModel):
    model_config = ConfigDict(protected_namespaces=())

    response: str = Field(
        ...,
        description="The AI-generated response",
    )
    model_used: str = Field(
        ...,
        description="The specific model that generated the response",
        examples=["claude-opus-4-6"],
    )
    provider: str = Field(
        ...,
        description="The AI provider used",
        examples=["anthropic"],
    )
    detected_intent: IntentType = Field(
        ...,
        description="The intent detected from the prompt",
    )
    response_time_ms: int = Field(
        ...,
        description="Total response time in milliseconds",
        examples=[1243],
    )

    model_config = ConfigDict(
        json_schema_extra={
            "examples": [
                {
                    "response": "Here is a Python function to reverse a string...",
                    "model_used": "claude-opus-4-6",
                    "provider": "anthropic",
                    "detected_intent": "code",
                    "response_time_ms": 1243,
                }
            ]
        }
    )