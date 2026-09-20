import sys
import json
import urllib.parse
import requests
import time

def load_cookie():
    # Sử dụng chung header với bot chính
    bot_path = "weibo_scanner_bot.py"
    with open(bot_path, 'r', encoding='utf-8') as f:
        content = f.read()
    # Trích xuất đoạn Cookie
    import re
    cookie_match = re.search(r"'Cookie':\s*'([^']+)'", content)
    if cookie_match:
        return cookie_match.group(1)
    return ""

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Vui lòng nhập tên diễn viên"}))
        return
        
    actor_name_b64 = sys.argv[1]
    import base64
    try:
        actor_name = base64.b64decode(actor_name_b64).decode('utf-8')
    except:
        actor_name = actor_name_b64
    
    with open('config.json', 'r', encoding='utf-8') as f:
        config = json.load(f)
        keywords = config.get('keywords', [])

    headers = {
        'User-Agent': 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
        'Accept': 'application/json, text/plain, */*',
        'Referer': 'https://m.weibo.cn/search',
        'X-Requested-With': 'XMLHttpRequest',
        'MWeibo-Pwa': '1',
        'X-XSRF-TOKEN': '7a9519',
        'Cookie': load_cookie()
    }

    # Bước 1: Tìm UID của diễn viên
    search_user_url = f"https://m.weibo.cn/api/container/getIndex?containerid=100103type%3D3%26q%3D{urllib.parse.quote(actor_name)}&page_type=searchall"
    
    try:
        res = requests.get(search_user_url, headers=headers, timeout=15)
        data = res.json()
    except Exception as e:
        print(json.dumps({"error": f"Lỗi kết nối khi tìm diễn viên: {str(e)}"}))
        return

    uid = None
    real_name = None
    if data.get('ok') == 1:
        for card in data.get('data', {}).get('cards', []):
            if card.get('card_type') == 11:
                card_group = card.get('card_group', [])
                for item in card_group:
                    if item.get('card_type') == 10:
                        user = item.get('user', {})
                        # Lấy người đầu tiên có tick V (0 = cá nhân)
                        if user.get('verified_type') == 0:
                            uid = user.get('id')
                            real_name = user.get('screen_name')
                            break
            if uid: break

    if not uid:
        print(json.dumps({"error": "Không tìm thấy tài khoản Weibo chính thức (có tick V) của diễn viên này."}))
        return

    # Bước 2: Quét tất cả từ khóa bên trong hồ sơ của diễn viên này
    violations = []
    
    for kw in keywords:
        time.sleep(2) # Tránh bị chặn
        kw_url = f"https://m.weibo.cn/api/container/getIndex?containerid=100103type%3D401%26uid%3D{uid}%26q%3D{urllib.parse.quote(kw)}"
        try:
            res2 = requests.get(kw_url, headers=headers, timeout=15)
            data2 = res2.json()
            if data2.get('ok') == 1:
                for card in data2.get('data', {}).get('cards', []):
                    if card.get('card_type') == 9:
                        mblog = card.get('mblog', {})
                        post_id = mblog.get('id')
                        violations.append({
                            'keyword': kw,
                            'post_id': post_id,
                            'link': f"https://weibo.com/{uid}/{post_id}",
                            'text': mblog.get('text', '')[:100] + "..."
                        })
                        break # Chỉ cần tìm thấy 1 bài vi phạm cho từ khóa này là đủ bằng chứng
        except:
            continue

    result = {
        "actor": real_name,
        "uid": uid,
        "violations": violations
    }
    
    print(json.dumps(result))

if __name__ == '__main__':
    main()
