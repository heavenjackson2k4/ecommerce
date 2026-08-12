from fastapi import FastAPI
app = FastAPI(title="Recommendation Service", version="1.0")
@app.get("/")
def root():
    return {"message": "Recommendation Service is running"}