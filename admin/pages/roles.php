<h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400 mb-8 tracking-tight">Phân Quyền Admin (RBAC)</h2>

<div class="bg-admin-panel backdrop-blur-2xl border border-admin-border rounded-[2rem] overflow-hidden shadow-2xl relative mb-10">
    <div class="absolute inset-0 bg-gradient-to-br from-white/[0.02] to-transparent pointer-events-none"></div>
    <div class="p-6 border-b border-white/5 flex justify-between items-center relative z-10">
        <div>
            <h3 class="text-lg font-bold text-white">Danh Sách Quản Trị Viên</h3>
            <p class="text-sm text-gray-400 mt-1">Cấp quyền hạn để chia sẻ công việc với các thành viên khác an toàn hơn.</p>
        </div>
        <button type="button" class="bg-gradient-to-r from-admin-primary to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-[0_0_20px_rgba(244,63,94,0.3)] hover:shadow-[0_0_25px_rgba(244,63,94,0.5)] flex items-center gap-2 transform hover:-translate-y-0.5">
            <i data-lucide="shield-plus" class="w-5 h-5"></i> Thêm Admin
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-400 relative z-10">
            <thead class="text-[11px] text-gray-400 uppercase tracking-widest bg-black/40 backdrop-blur-md border-b border-white/5">
                <tr>
                    <th scope="col" class="px-8 py-5 font-bold">Tài Khoản</th>
                    <th scope="col" class="px-6 py-5 font-bold text-center">Vai Trò</th>
                    <th scope="col" class="px-6 py-5 font-bold text-center">Trạng Thái</th>
                    <th scope="col" class="px-8 py-5 font-bold text-right">Thao Tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <tr class="hover:bg-white/[0.02] transition-colors duration-300 group/row">
                    <td class="px-8 py-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 rounded-full bg-rose-500/20 text-rose-500 flex items-center justify-center font-bold text-lg">A</div>
                            <div>
                                <div class="text-white font-bold text-base mb-0.5">admin</div>
                                <div class="text-xs text-gray-500 font-medium">Root Admin (Tạo lúc cài đặt)</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-rose-500/10 border border-rose-500/20 text-rose-400 px-3 py-1.5 rounded-md text-xs font-bold shadow-[0_0_10px_rgba(244,63,94,0.1)] uppercase tracking-wider">Super Admin</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-emerald-400 font-bold flex justify-center items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></div> Đang hoạt động</span>
                    </td>
                    <td class="px-8 py-4 text-right">
                        <span class="text-xs text-gray-500 italic">Không thể sửa Root Admin</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
    <div class="bg-admin-panel backdrop-blur-xl border border-admin-border p-6 rounded-2xl">
        <h4 class="text-rose-400 font-bold mb-2 flex items-center gap-2"><i data-lucide="shield-alert" class="w-5 h-5"></i> Super Admin</h4>
        <p class="text-sm text-gray-400">Toàn quyền kiểm soát hệ thống, có thể thêm tài khoản admin khác, thay đổi cài đặt sâu.</p>
    </div>
    <div class="bg-admin-panel backdrop-blur-xl border border-admin-border p-6 rounded-2xl">
        <h4 class="text-blue-400 font-bold mb-2 flex items-center gap-2"><i data-lucide="edit-3" class="w-5 h-5"></i> Editor (Biên Tập)</h4>
        <p class="text-sm text-gray-400">Có quyền thêm, sửa, xóa phim, cào dữ liệu, duyệt bình luận. Không thể truy cập Cài đặt/SEO.</p>
    </div>
    <div class="bg-admin-panel backdrop-blur-xl border border-admin-border p-6 rounded-2xl">
        <h4 class="text-emerald-400 font-bold mb-2 flex items-center gap-2"><i data-lucide="bar-chart" class="w-5 h-5"></i> SEO Manager</h4>
        <p class="text-sm text-gray-400">Có quyền xem thống kê, sửa meta tags SEO, cấu hình sitemap và robots.txt.</p>
    </div>
</div>
