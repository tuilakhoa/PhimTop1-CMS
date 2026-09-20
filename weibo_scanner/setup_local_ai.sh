#!/bin/bash
# Lấy thư mục hiện tại của script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$DIR"

echo "[+] Kiểm tra môi trường ảo (venv)..."
if [ ! -d "venv" ]; then
    echo "[+] Đang tạo môi trường ảo mới..."
    python3 -m venv venv
fi

source venv/bin/activate

echo "[+] Cài đặt huggingface-hub..."
pip install huggingface-hub

echo "[+] Cài đặt llama-cpp-python (Cmake/GCC compiler)..."
CMAKE_ARGS="-DGGML_NATIVE=OFF" pip install llama-cpp-python

echo "[+] Đang tải mô hình AI..."
python3 setup_local_ai.py
