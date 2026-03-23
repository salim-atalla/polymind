import pytest
from unittest.mock import AsyncMock, patch, MagicMock
from app.services.analyzer import analyze_intent
from app.models.schemas import IntentType

def make_mock_response(intent: str, confidence: float = 0.95):
    import json
    mock_response = MagicMock()
    mock_response.choices[0].message.content = json.dumps({
        "intent": intent,
        "confidence": confidence
    })
    return mock_response

@pytest.mark.asyncio
async def test_analyze_code_intent():
    with patch("app.services.analyzer.client.chat.completions.create",
               new_callable=AsyncMock) as mock_create:
        mock_create.return_value = make_mock_response("code")
        result = await analyze_intent("Write a Python function to sort a list")
        assert result == IntentType.CODE

@pytest.mark.asyncio
async def test_analyze_text_intent():
    with patch("app.services.analyzer.client.chat.completions.create",
               new_callable=AsyncMock) as mock_create:
        mock_create.return_value = make_mock_response("text")
        result = await analyze_intent("Write me a professional email")
        assert result == IntentType.TEXT

@pytest.mark.asyncio
async def test_analyze_image_intent():
    with patch("app.services.analyzer.client.chat.completions.create",
               new_callable=AsyncMock) as mock_create:
        mock_create.return_value = make_mock_response("image")
        result = await analyze_intent("Generate an image of a sunset")
        assert result == IntentType.IMAGE

@pytest.mark.asyncio
async def test_fallback_on_invalid_json():
    with patch("app.services.analyzer.client.chat.completions.create",
               new_callable=AsyncMock) as mock_create:
        mock_response = MagicMock()
        mock_response.choices[0].message.content = "invalid json"
        mock_create.return_value = mock_response
        result = await analyze_intent("anything")
        assert result == IntentType.GENERAL

@pytest.mark.asyncio
async def test_fallback_on_api_error():
    with patch("app.services.analyzer.client.chat.completions.create",
               new_callable=AsyncMock) as mock_create:
        mock_create.side_effect = Exception("API error")
        result = await analyze_intent("anything")
        assert result == IntentType.GENERAL