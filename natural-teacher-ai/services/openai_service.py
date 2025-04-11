import os
from typing import Dict, Any, List
import openai
from langchain_community.chat_models import ChatOpenAI 
from langchain.schema import HumanMessage, SystemMessage
import json
import logging
import asyncio
from typing import AsyncGenerator
logger = logging.getLogger(__name__)

class OpenAIService:
    def __init__(self, api_key=None):
        self.api_key = api_key or os.getenv("OPENAI_API_KEY")
        if not self.api_key:
            raise ValueError("OpenAI API key is required")

        openai.api_key = self.api_key

        self.chat_model = ChatOpenAI(
            openai_api_key=self.api_key,
            model="gpt-4o-mini",
            temperature=0.7
        )
    async def get_completion_streaming(self, prompt) -> AsyncGenerator[str, None]:
        """Get completion from OpenAI API with streaming output"""
        try:
            # Set up streaming parameters
            kwargs = {
                "model": self.model,
                "messages": [{"role": "user", "content": prompt}],
                "temperature": 0.7,
                "max_tokens": 2000,
                "stream": True  # Enable streaming
            }
            
            # Call the OpenAI API with streaming
            response = await self.client.chat.completions.create(**kwargs)
            
            async for chunk in response:
                # Extract and yield the content from each chunk
                if chunk.choices and chunk.choices[0].delta.content:
                    yield chunk.choices[0].delta.content
                    
        except Exception as e:
            # Log the error but continue with a fallback message
            self.logger.error(f"Error getting streaming completion: {e}")
            yield "I apologize, but I encountered an issue while generating a response. "
            yield "Let me try to help you in a different way."
    def get_llm(self):
        """Return the LangChain ChatOpenAI instance for CrewAI"""
        return self.chat_model
    
    def get_completion(self, prompt: str, system_message: str = None) -> str:
        try:
            messages = []
            if system_message:
                messages.append({"role": "system", "content": system_message})
            messages.append({"role": "user", "content": prompt})

            response = openai.ChatCompletion.create(
                model="gpt-4o-mini",
                messages=messages,
                temperature=0.7,
                max_tokens=2000
            )

            return response["choices"][0]["message"]["content"]
        except Exception as e:
            logger.error(f"Error getting completion: {str(e)}")
            raise

    
    def format_for_age(self, content: str, age: int) -> str:
        """Format content to be age-appropriate"""
        try:
            system_message = """
            You are an educational content adapter. Your task is to rewrite 
            the provided content to make it age-appropriate and engaging 
            for the student's age while preserving all the educational value.
            """
            
            prompt = f"""
            Please rewrite the following educational content to be appropriate
            and engaging for a {age}-year-old student:
            
            {content}
            
            Make sure to:
            1. Use vocabulary appropriate for the age
            2. Keep sentences shorter for younger children
            3. Use engaging language and examples
            4. Maintain all educational content and accuracy
            5. Include encouraging language for younger children
            """
            
            return self.get_completion(prompt, system_message)
        except Exception as e:
            logger.error(f"Error formatting for age: {str(e)}")
            # Return original content if there's an error
            return content