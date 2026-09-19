const fs = require('fs');
const path = '/www/wwwroot/api.phimtop1.asia/views/api-document.ejs';
let content = fs.readFileSync(path, 'utf8');

// List JSON
const listTarget = /<h3 class="text-white font-bold mb-2">Cấu trúc JSON trả về \(Response\):<\/h3>\s*<h3 class="text-white font-bold mt-8 mb-4">Code mẫu tích hợp \(Lấy Danh Sách\):<\/h3>/;

const listJsonHtml = `<h3 class="text-white font-bold mb-2">Cấu trúc JSON trả về (Response):</h3>
            <div class="bg-[#0b0c0f] border border-[#272a30] rounded-xl p-4 mb-8 overflow-x-auto custom-scrollbar">
            <pre><code>{
  "status": true,
  "data": {
    "items": [
      {
        "name": "Tên Phim",
        "slug": "ten-phim",
        "year": 2026,
        "origin_name": "Original Name",
        "status": "completed",
        "type": "series",
        "thumb_url": "https://...",
        "episode_current": "Hoàn Tất (24/24)",
        "modified": "2026-09-12T12:00:00Z"
      }
    ],
    "pagination": {
      "totalItems": 10500,
      "totalItemsPerPage": 24,
      "currentPage": 1,
      "totalPages": 438
    }
  }
}</code></pre>
            </div>
            <h3 class="text-white font-bold mt-8 mb-4">Code mẫu tích hợp (Lấy Danh Sách):</h3>`;

content = content.replace(listTarget, listJsonHtml);

// Detail JSON
const detailTarget = /<h3 class="text-white font-bold mb-2">Cấu trúc JSON trả về \(Response\):<\/h3>\s*<h3 class="text-white font-bold mt-8 mb-4">Code mẫu tích hợp \(Lấy Chi Tiết\):<\/h3>/;

const detailJsonHtml = `<h3 class="text-white font-bold mb-2">Cấu trúc JSON trả về (Response):</h3>
            <div class="bg-[#0b0c0f] border border-[#272a30] rounded-xl p-4 mb-8 overflow-x-auto custom-scrollbar">
            <pre><code>{
  "status": true,
  "msg": "",
  "movie": {
    "id": 123,
    "name": "Đấu Phá Thương Khung",
    "slug": "dau-pha-thuong-khung",
    "content": "Nội dung chi tiết phim...",
    "actor": "Tiêu Viêm, Huân Nhi...",
    "categories": [{"id":"...","name":"Hành Động","slug":"hanh-dong"}],
    "countries": [{"id":"...","name":"Trung Quốc","slug":"trung-quoc"}]
  },
  "episodes": [
    {
      "server_name": "VIP #1",
      "server_data": [
        {
          "name": "Tập 1",
          "slug": "tap-1",
          "filename": "",
          "link_embed": "https://.../embed",
          "link_m3u8": "https://.../master.m3u8"
        }
      ]
    }
  ]
}</code></pre>
            </div>
            <h3 class="text-white font-bold mt-8 mb-4">Code mẫu tích hợp (Lấy Chi Tiết):</h3>`;

content = content.replace(detailTarget, detailJsonHtml);
fs.writeFileSync(path, content);
console.log('Docs Fixed');
