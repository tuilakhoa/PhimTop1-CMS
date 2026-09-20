import requests
from PIL import Image
import imagehash
import io
import time
import json
import re
import os
from datetime import datetime

class AdvancedWeiboScanner:
    def __init__(self, config_path="config.json", status_file="status.json"):
        self.status_file = status_file
        self.update_status("khởi động", "Hệ thống đang tải cấu hình và ảnh mẫu...", 0, 0)
        
        print("\n" + "="*60)
        print("🚀 KHỞI ĐỘNG HỆ THỐNG QUÉT WEIBO 🚀")
        print("="*60)
        self.load_config(config_path)
        
        regex_pattern = "|".join(map(re.escape, self.config['keywords']))
        self.keyword_regex = re.compile(regex_pattern, re.IGNORECASE)
        
        self.reference_hashes = []
        for img_path in self.config.get('reference_images', []):
            if os.path.exists(img_path):
                hash_val = imagehash.phash(Image.open(img_path))
                self.reference_hashes.append(hash_val)
                print(f"[+] Đã tải ảnh mẫu: {img_path}")
            else:
                print(f"[-] Cảnh báo: Không tìm thấy ảnh {img_path}")

        self.headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/91.0.4472.124 Safari/537.36',
        }
        self.update_status("sẵn sàng", "Hoàn tất nạp dữ liệu. Bắt đầu quét...", 0, len(self.config.get('target_uids', [])))
        print("="*60 + "\n")

    def update_status(self, state, message, current_step, total_steps, current_uid="", current_name=""):
        """Ghi tiến trình ra file JSON để Admin Dashboard (PHP) có thể đọc được"""
        status_data = {
            "state": state, # "khởi động", "đang quét", "hoàn thành", "lỗi"
            "message": message,
            "progress": f"{current_step}/{total_steps}",
            "percentage": int((current_step / total_steps * 100)) if total_steps > 0 else 0,
            "current_target": {
                "uid": current_uid,
                "name": current_name
            },
            "last_updated": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        }
        try:
            with open(self.status_file, 'w', encoding='utf-8') as f:
                json.dump(status_data, f, ensure_ascii=False, indent=4)
        except Exception as e:
            pass # Bỏ qua lỗi ghi file nếu có

    def save_results(self, results):
        """Lưu danh sách vi phạm ra file kết quả để CMS đọc"""
        with open("violators_result.json", 'w', encoding='utf-8') as f:
            json.dump(results, f, ensure_ascii=False, indent=4)

    def load_config(self, path):
        with open(path, 'r', encoding='utf-8') as f:
            self.config = json.load(f)
        print(f"[+] Đã tải cấu hình: {len(self.config['keywords'])} từ khóa, {len(self.config['target_uids'])} tài khoản mục tiêu.")

    def fetch_user_info(self, uid):
        url = f"https://m.weibo.cn/api/container/getIndex?type=uid&value={uid}"
        try:
            res = requests.get(url, headers=self.headers, timeout=10)
            data = res.json()
            if data.get('ok') == 1:
                user_info = data.get('data', {}).get('userInfo', {})
                return {
                    'name': user_info.get('screen_name', 'Không xác định'),
                    'verified': user_info.get('verified', False),
                    'verified_type': user_info.get('verified_type', -1),
                    'verified_reason': user_info.get('verified_reason', 'Không có')
                }
            return None
        except Exception:
            return None

    def fetch_posts(self, uid):
        url = f"https://m.weibo.cn/api/container/getIndex?type=uid&value={uid}&containerid=107603{uid}"
        try:
            res = requests.get(url, headers=self.headers, timeout=10)
            data = res.json()
            posts = []
            if data.get('ok') == 1:
                for card in data.get('data', {}).get('cards', []):
                    if card.get('card_type') == 9:
                        mblog = card.get('mblog', {})
                        pic_urls = [p.get('large', {}).get('url') for p in mblog.get('pics', [])]
                        posts.append({
                            'id': mblog.get('id'),
                            'text': mblog.get('text', ''),
                            'pics': pic_urls
                        })
            return posts
        except Exception:
            return []

    def check_images(self, image_url, cutoff=5):
        if not self.reference_hashes: return False
        try:
            res = requests.get(image_url, headers=self.headers, timeout=10)
            post_hash = imagehash.phash(Image.open(io.BytesIO(res.content)))
            for ref_hash in self.reference_hashes:
                if (ref_hash - post_hash) <= cutoff: return True 
            return False
        except Exception: return False

    def scan(self):
        results = []
        uids = self.config.get('target_uids', [])
        total = len(uids)
        
        for index, uid in enumerate(uids, 1):
            self.update_status("đang quét", f"Đang lấy thông tin UID: {uid}", index, total, uid, "Đang xử lý...")
            print(f"[{index}/{total}] TIẾN HÀNH QUÉT UID: {uid} ".ljust(60, "-"))
            
            user_info = self.fetch_user_info(uid)
            if not user_info:
                print("  -> ❌ Không thể lấy thông tin. Bỏ qua.")
                continue
                
            name = user_info['name']
            is_verified = user_info['verified']
            
            self.update_status("đang quét", "Đang tải danh sách bài đăng...", index, total, uid, name)
            print(f"  -> 🏷️  Tên: {name} | Đang tải bài đăng...")
            
            if not is_verified or user_info['verified_type'] != 0:
                print("  -> ⏭️ Bỏ qua: Không phải Nghệ sĩ/Ca sĩ.")
                continue
                
            posts = self.fetch_posts(uid)
            self.update_status("đang quét", f"Đang phân tích {len(posts)} bài đăng...", index, total, uid, name)
            
            for post in posts:
                reasons = []
                
                found_keywords = self.keyword_regex.findall(post['text'])
                if found_keywords:
                    unique_keywords = list(set(found_keywords))
                    reasons.append(f"Từ khóa: {', '.join(unique_keywords)}")
                
                if not found_keywords and post['pics']:
                    for pic_url in post['pics']:
                        if self.check_images(pic_url):
                            reasons.append("Ảnh vi phạm")
                            break
                
                if reasons:
                    results.append({
                        'uid': uid,
                        'name': name,
                        'post_id': post['id'],
                        'reason': " | ".join(reasons),
                        'link': f"https://weibo.com/{uid}/{post['id']}"
                    })
            
            self.update_status("đang quét", f"Đang chờ để tránh giới hạn kết nối...", index, total, uid, name)
            time.sleep(3) 
            
        self.update_status("hoàn thành", f"Quét xong. Phát hiện {len(results)} vi phạm.", total, total)
        self.save_results(results)
        return results

if __name__ == "__main__":
    if os.path.exists("config.json"):
        scanner = AdvancedWeiboScanner("config.json")
        violators = scanner.scan()
        print("="*60)
        print(f"🏆 TỔNG KẾT KẾT QUẢ (PHÁT HIỆN {len(violators)} VI PHẠM) 🏆")
        print("="*60)
    else:
        print("[-] Vui lòng tạo file config.json")
