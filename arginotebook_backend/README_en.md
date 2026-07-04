1. General Information

This folder contains the backend for Wikicrop.

Run the following command to install the required libraries:

```bash
pip install -r requirements.txt
```

Refer to the HuongDanCaiDat_WikiNotebook.pdf document in the root folder for more installation details.

2. Folder Structure

arginotebook_be/
|
|-- api.py                 # Main entry file (initializes the FastAPI app)
|
|-- routers/               # Routing layer (endpoints): receives requests and returns responses
|   |-- notebooks.py       # Notebook management APIs: source info, notebooks
|   |-- contents.py        # Article management APIs: article info, chunks, ...
|   `-- generate.py        # API that triggers the article generation process
|
|-- services/              # Logic layer (controller/service): the heart of the system
|   `-- ai_generation.py   # Connects data from MySQL, calls VectorDB and LLM
|
|-- databases/             # Database layer (CRUD): contains MySQL operations only
|   |-- crud_notebook.py   # SELECT, INSERT, UPDATE commands for the notebooks table
|   `-- connection.py      # MySQL connection configuration (SQLAlchemy/PyMySQL)
|
|-- schemas/               # Pydantic layer: declares input/output data formats for validation
|
`-- ai_core/               # AI model layer
    |-- preprocessor.py    # Chunking, embedding, storing in ChromaDB
    |-- wiki_composer.py   # Article-writing agent, RAG logic
    |-- llm_engine.py      # Interface with the LLM API
    `-- extractor.py       # PDF, web, and YouTube extraction
