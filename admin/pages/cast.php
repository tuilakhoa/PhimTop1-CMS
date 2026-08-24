<h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400 mb-8 tracking-tight">Quản Lý Diễn Viên & Đạo Diễn</h2>

<div class="bg-admin-panel backdrop-blur-xl border border-admin-border rounded-[2rem] p-6 mb-10 shadow-2xl relative overflow-hidden group">
    <div class="absolute -top-32 -right-32 w-64 h-64 bg-admin-primary/5 rounded-full blur-[80px] pointer-events-none group-hover:bg-admin-primary/10 transition-colors duration-700"></div>
    <form method="GET" action="" class="flex items-center gap-4 relative z-10">
        <input type="hidden" name="page" value="cast">
        <div class="flex-1 relative group/input">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-transform group-focus-within/input:scale-110">
                <i data-lucide="search" class="w-5 h-5 text-gray-500 group-focus-within/input:text-admin-primary transition-colors"></i>
            </div>
            <input type="text" name="q" placeholder="Tìm kiếm tên diễn viên/đạo diễn..." class="w-full bg-black/40 backdrop-blur-sm border border-white/10 text-white rounded-xl pl-12 pr-4 py-3.5 focus:outline-none focus:border-admin-primary focus:ring-1 focus:ring-admin-primary transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.3)]">
        </div>
        <button type="submit" class="bg-gradient-to-r from-admin-primary to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-bold py-3.5 px-8 rounded-xl transition-all shadow-[0_0_20px_rgba(244,63,94,0.3)] hover:shadow-[0_0_25px_rgba(244,63,94,0.5)] flex items-center gap-2 transform hover:-translate-y-0.5">
            <i data-lucide="search" class="w-4 h-4"></i> Tìm Kiếm
        </button>
        <button type="button" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-8 rounded-xl transition-all shadow-[0_0_20px_rgba(16,185,129,0.3)] hover:shadow-[0_0_25px_rgba(16,185,129,0.5)] flex items-center gap-2 transform hover:-translate-y-0.5">
            <i data-lucide="plus" class="w-4 h-4"></i> Thêm Mới
        </button>
    </form>
</div>

<div class="bg-admin-panel backdrop-blur-2xl border border-admin-border rounded-[2rem] overflow-hidden shadow-2xl relative">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-400 relative z-10">
            <thead class="text-[11px] text-gray-400 uppercase tracking-widest bg-black/40 backdrop-blur-md border-b border-white/5">
                <tr>
                    <th scope="col" class="px-8 py-5 font-bold">Hồ Sơ</th>
                    <th scope="col" class="px-6 py-5 font-bold text-center">Vai Trò</th>
                    <th scope="col" class="px-6 py-5 font-bold text-center">Ngày Sinh</th>
                    <th scope="col" class="px-6 py-5 font-bold text-center">Số Phim Tham Gia</th>
                    <th scope="col" class="px-8 py-5 font-bold text-right">Thao Tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <tr class="hover:bg-white/[0.02] transition-colors duration-300 group/row">
                    <td class="px-8 py-4">
                        <div class="flex items-center space-x-5">
                            <div class="relative w-12 h-12 rounded-full overflow-hidden shadow-lg bg-black/50 shrink-0 border border-white/5 group-hover/row:border-admin-primary/30 transition-colors">
                                <div class="w-full h-full bg-gray-800 flex items-center justify-center text-gray-500"><i data-lucide="user" class="w-6 h-6"></i></div>
                            </div>
                            <div>
                                <div class="text-white font-bold text-base mb-1 group-hover/row:text-admin-primary transition-colors tracking-wide">
                                    Mẫu Diễn Viên (Demo)
                                </div>
                                <div class="text-xs text-gray-500 font-medium tracking-wide">Quốc tịch: US</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-blue-500/10 border border-blue-500/20 text-blue-400 px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap shadow-[0_0_10px_rgba(59,130,246,0.1)]">Diễn Viên</span>
                    </td>
                    <td class="px-6 py-4 text-center font-medium text-gray-300">01/01/1990</td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-emerald-400 font-bold">12</span> phim
                    </td>
                    <td class="px-8 py-4 text-right">
                        <div class="flex items-center justify-end space-x-2 opacity-0 group-hover/row:opacity-100 transition-opacity duration-300">
                            <button class="text-blue-400 hover:text-blue-300 hover:bg-blue-400/10 transition-colors p-2 rounded-lg" title="Sửa">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </button>
                            <button class="text-red-400 hover:text-red-300 hover:bg-red-400/10 transition-colors p-2 rounded-lg" title="Xóa">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
