import json
import os
import subprocess
import time
import base64

def load_queue():
    with open('task_queue.json', 'r', encoding='utf-8') as f:
        return json.load(f)

def save_queue(q):
    with open('task_queue.json', 'w', encoding='utf-8') as f:
        json.dump(q, f, ensure_ascii=False, indent=2)

def main():
    q = load_queue()
    
    # Ưu tiên tác vụ Investigate trước vì nó cần phản hồi ngay
    if q.get('investigate', {}).get('status') == 'pending':
        q['investigate']['status'] = 'processing'
        save_queue(q)
        
        actor_name = q['investigate']['name']
        b64_name = base64.b64encode(actor_name.encode('utf-8')).decode('utf-8')
        
        # Chạy lệnh
        python_bin = './venv/bin/python3'
        if not os.path.exists(python_bin):
            python_bin = 'python3'
            
        try:
            result = subprocess.run([python_bin, 'investigate_actor.py', b64_name], capture_output=True, text=True, timeout=120)
            
            output = result.stdout.strip()
            
            # Cập nhật kết quả lại vào file
            try:
                parsed_res = json.loads(output)
                q['investigate']['status'] = 'completed'
                q['investigate']['result'] = parsed_res
            except Exception as e:
                q['investigate']['status'] = 'error'
                q['investigate']['result'] = f"RAW Error: {output} (Stderr: {result.stderr})"
                
        except Exception as e:
            q['investigate']['status'] = 'error'
            q['investigate']['result'] = str(e)
            
        save_queue(q)
        return # Chỉ chạy 1 tác vụ mỗi lần cronjob gọi
        
    # Nếu không có investigate, chạy scan nền
    if q.get('scan', {}).get('status') == 'pending':
        q['scan']['status'] = 'processing'
        save_queue(q)
        
        python_bin = './venv/bin/python3'
        if not os.path.exists(python_bin):
            python_bin = 'python3'
            
        subprocess.Popen([python_bin, 'weibo_scanner_bot.py'])
        
        # Không chờ scan kết thúc vì nó rất lâu, script này sẽ thoát để không nghẽn Cron
        q['scan']['status'] = 'completed'
        save_queue(q)
        return

if __name__ == '__main__':
    main()
