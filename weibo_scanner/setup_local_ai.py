import os
import json
from huggingface_hub import hf_hub_download

print("[+] Tải mô hình Qwen2.5-0.5B-Instruct (siêu nhẹ, tối ưu tiếng Trung)...")
try:
    model_path = hf_hub_download(
        repo_id="Qwen/Qwen2.5-0.5B-Instruct-GGUF",
        filename="qwen2.5-0.5b-instruct-q8_0.gguf",
        local_dir="/home/khoa/PhimTop1-CMS/weibo_scanner/models",
        local_dir_use_symlinks=False
    )
    print(f"[+] Tải thành công! Mô hình lưu tại: {model_path}")
    
    config_file = "/home/khoa/PhimTop1-CMS/weibo_scanner/config.json"
    if os.path.exists(config_file):
        with open(config_file, 'r', encoding='utf-8') as f:
            cfg = json.load(f)
        cfg['ai_provider'] = 'local'
        cfg['local_model_path'] = model_path
        with open(config_file, 'w', encoding='utf-8') as f:
            json.dump(cfg, f, ensure_ascii=False, indent=4)
        print("[+] Đã cấu hình CMS tự động chuyển sang mô hình Local!")
except Exception as e:
    print(f"[-] Lỗi tải mô hình: {e}")
