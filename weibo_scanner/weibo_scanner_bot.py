import requests
import time
import json
import os
from datetime import datetime
import urllib.parse

class GlobalWeiboScanner:
    def __init__(self, config_path="config.json", status_file="status.json"):
        self.status_file = status_file
        self.update_status("khởi động", "Hệ thống đang tải cấu hình...", 0, 0)
        
        print("\n" + "="*60)
        print("🚀 KHỞI ĐỘNG HỆ THỐNG QUÉT TOÀN MẠNG WEIBO (PHƯƠNG PHÁP 2) 🚀")
        print("="*60)
        self.load_config(config_path)

        self.headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
            'Accept': 'application/json, text/plain, */*',
            'Referer': 'https://m.weibo.cn/search',
        }
        self.update_status("sẵn sàng", "Sẵn sàng quét theo từ khóa...", 0, len(self.config.get('keywords', [])))
        print("="*60 + "\n")

    def update_status(self, state, message, current_step, total_steps, current_keyword="", found_count=0):
        status_data = {
            "state": state,
            "message": message,
            "progress": f"{current_step}/{total_steps}",
            "percentage": int((current_step / total_steps * 100)) if total_steps > 0 else 0,
            "current_target": {
                "keyword": current_keyword,
                "found_artists": found_count
            },
            "last_updated": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        }
        try:
            with open(self.status_file, 'w', encoding='utf-8') as f:
                json.dump(status_data, f, ensure_ascii=False, indent=4)
        except Exception:
            pass 

    def save_results(self, new_results):
        """Lưu và nối thêm kết quả vi phạm (rất quan trọng khi chạy Cronjob để không mất dữ liệu cũ)"""
        result_file = "violators_result.json"
        existing_results = []
        
        # Đọc dữ liệu cũ nếu có
        if os.path.exists(result_file):
            try:
                with open(result_file, 'r', encoding='utf-8') as f:
                    existing_results = json.load(f)
            except Exception:
                pass
                
        # Gộp dữ liệu mới vào dữ liệu cũ
        all_results = existing_results + new_results
        
        # Lọc trùng lặp (tránh 1 bài viết bị lưu 2 lần nếu 2 lần quét cách nhau quá gần)
        unique_results = {item['post_id']: item for item in all_results}.values()
        
        with open(result_file, 'w', encoding='utf-8') as f:
            json.dump(list(unique_results), f, ensure_ascii=False, indent=4)

    def load_config(self, path):
        with open(path, 'r', encoding='utf-8') as f:
            self.config = json.load(f)
        print(f"[+] Đã tải cấu hình: {len(self.config['keywords'])} từ khóa.")

    def search_keyword(self, keyword, page=1):
        """Sử dụng API tìm kiếm của Weibo để lấy bài viết theo từ khóa"""
        encoded_kw = urllib.parse.quote(keyword)
        url = f"https://m.weibo.cn/api/container/getIndex?containerid=100103type%3D1%26q%3D{encoded_kw}&page_type=searchall&page={page}"
        try:
            res = requests.get(url, headers=self.headers, timeout=15)
            data = res.json()
            posts = []
            if data.get('ok') == 1:
                for card in data.get('data', {}).get('cards', []):
                    # card_type == 9 là bài đăng thông thường
                    if card.get('card_type') == 9:
                        mblog = card.get('mblog', {})
                        user = mblog.get('user', {})
                        posts.append({
                            'post_id': mblog.get('id'),
                            'text': mblog.get('text', ''),
                            'user_id': user.get('id'),
                            'user_name': user.get('screen_name', ''),
                            'verified': user.get('verified', False),
                            'verified_type': user.get('verified_type', -1),
                            'verified_reason': user.get('verified_reason', '')
                        })
            return posts
        except Exception as e:
            print(f"  [-] Lỗi kết nối khi tìm kiếm: {e}")
            return []

    def scan(self):
        results = []
        # Chống trùng lặp nghệ sĩ (nếu 1 người dùng nhiều từ khóa)
        found_uids = set() 
        
        keywords = self.config.get('keywords', [])
        total = len(keywords)
        
        for index, keyword in enumerate(keywords, 1):
            print(f"[{index}/{total}] ĐANG TÌM KIẾM TRÊN TOÀN MẠNG: {keyword} ".ljust(60, "-"))
            self.update_status("đang quét", f"Đang quét diện rộng từ khóa: {keyword}", index, total, keyword, len(results))
            
            # Quét 5 trang đầu tiên của kết quả tìm kiếm (Mỗi trang có khoảng 10 bài)
            for page in range(1, 6):
                msg = f"Đang duyệt Trang {page}/5 (Từ khóa: {keyword})"
                print(f"  -> {msg}...")
                self.update_status("đang quét", msg, index, total, keyword, len(results))
                
                posts = self.search_keyword(keyword, page)
                
                if not posts:
                    break # Không còn kết quả
                
                for post in posts:
                    uid = str(post['user_id'])
                    is_verified = post['verified']
                    v_type = post['verified_type']
                    
                    # CHỈ LỌC NGHỆ SĨ / CA SĨ (Bỏ qua người thường và công ty)
                    if is_verified and v_type == 0:
                        if uid not in found_uids:
                            found_uids.add(uid)
                            print(f"  -> ⚠️ [PHÁT HIỆN] Nghệ sĩ: {post['user_name']} (Lý do xác minh: {post['verified_reason']})")
                            results.append({
                                'uid': uid,
                                'name': post['user_name'],
                                'user_type': f"⭐ Nghệ sĩ ({post['verified_reason']})",
                                'post_id': post['post_id'],
                                'keyword_matched': keyword,
                                'link': f"https://weibo.com/{uid}/{post['post_id']}"
                            })
                
                # Nghỉ 2 giây giữa mỗi trang tìm kiếm để tránh bị khóa IP
                time.sleep(2)
            
            print(f"  -> ✅ Hoàn thành từ khóa. Nghỉ 5 giây trước khi sang từ khác...")
            time.sleep(5) 
            
        self.update_status("hoàn thành", f"Quét xong. Phát hiện {len(results)} nghệ sĩ vi phạm.", total, total, "Hoàn tất", len(results))
        self.save_results(results)
        return results

if __name__ == "__main__":
    if os.path.exists("config.json"):
        scanner = GlobalWeiboScanner("config.json")
        violators = scanner.scan()
        
        print("="*60)
        print(f"🏆 TỔNG KẾT (PHÁT HIỆN {len(violators)} NGHỆ SĨ VI PHẠM) 🏆")
        print("="*60)
        
        if not violators:
            print("Không tìm thấy nghệ sĩ nào từ các từ khóa này trong các kết quả tìm kiếm gần đây.")
        else:
            for v in violators:
                print(f"- Nghệ sĩ: {v['name']} (UID: {v['uid']})")
                print(f"  Loại TK: {v['user_type']}")
                print(f"  Từ khóa: Dính từ khóa '{v['keyword_matched']}'")
                print(f"  Link   : {v['link']}\n")
    else:
        print("[-] Vui lòng tạo file config.json")
