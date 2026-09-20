#!/bin/bash
cd /home/khoa/PhimTop1-CMS/weibo_scanner
source venv/bin/activate
echo "[+] Cài đặt huggingface-hub..."
pip install huggingface-hub
echo "[+] Cài đặt llama-cpp-python (Cmake/GCC compiler)..."
CMAKE_ARGS="-DGGML_NATIVE=OFF" pip install llama-cpp-python
python3 setup_local_ai.py
