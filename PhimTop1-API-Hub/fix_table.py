import re

with open('views/index.ejs', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace Table Header
old_thead = """<th class="px-6 py-4 w-1/3">THÔNG TIN</th>
                            <th class="px-4 py-4 text-center">NĂM</th>
                            <th class="px-4 py-4">TÌNH TRẠNG</th>
                            <th class="px-4 py-4 text-center">ĐỊNH DẠNG</th>
                            <th class="px-6 py-4 text-right">CẬP NHẬT</th>"""

new_thead = """<th class="px-6 py-4 w-1/3">THÔNG TIN</th>
                            <th class="px-4 py-4 text-center">NĂM</th>
                            <th class="px-4 py-4 text-center">TÌNH TRẠNG</th>
                            <th class="px-4 py-4 text-center">TMDB</th>
                            <th class="px-4 py-4 text-center">IMDB</th>
                            <th class="px-4 py-4 text-center">ĐỊNH DẠNG</th>
                            <th class="px-4 py-4 text-center">QUỐC GIA</th>
                            <th class="px-6 py-4 text-right">CẬP NHẬT</th>"""
content = content.replace(old_thead, new_thead)

# Replace Table Row Body
# Find where <td class="px-4 py-3"> status </td> ends and <td text-center ĐỊNH DẠNG begins
# Let's do it precisely using regex.
import re

td_year = '<td class="px-4 py-3 text-center text-gray-300 font-medium"><%= movie.year || \'N/A\' %></td>'

status_col_regex = r'(<td class="px-4 py-3">\s*<%\s*let st = movie\.status \|\| \'N/A\';[\s\S]*?</span>\s*</td>)'

type_col_regex = r'(<td class="px-4 py-3 text-center text-gray-400 text-xs font-bold uppercase">\s*<%= movie\.type === \'series\' \? \'PHIM BỘ\' : \(movie\.type === \'single\' \? \'PHIM LẺ\' : \'HOẠT HÌNH\'\) %>\s*</td>)'

def replacer(match):
    status_td = match.group(1)
    type_td = match.group(2)
    
    # We will inject the new TDs right between them!
    tmdb_imdb = """
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold rounded border border-blue-500/30 text-blue-400 bg-blue-500/10 whitespace-nowrap">
                                            <i data-lucide="bar-chart-2" class="w-3 h-3"></i> <%= movie.tmdb_vote ? parseFloat(movie.tmdb_vote).toFixed(1) : 'N/A' %>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold rounded border border-yellow-500/30 text-yellow-400 bg-yellow-500/10 whitespace-nowrap">
                                            <i data-lucide="star" class="w-3 h-3"></i> <%= movie.imdb_vote ? parseFloat(movie.imdb_vote).toFixed(1) : 'N/A' %>
                                        </span>
                                    </td>"""
    
    country_code = """
                                    <td class="px-4 py-3 text-center">
                                        <%
                                        let countryCode = '🏳️';
                                        if (movie.countries_json) {
                                            try {
                                                let countriesArr = JSON.parse(movie.countries_json);
                                                if(countriesArr.length > 0) {
                                                    let slug = countriesArr[0].slug;
                                                    const flagMap = {
                                                        'han-quoc': '🇰🇷', 'trung-quoc': '🇨🇳', 'nhat-ban': '🇯🇵', 'thai-lan': '🇹🇭',
                                                        'au-my': '🇺🇸', 'viet-nam': '🇻🇳', 'hong-kong': '🇭🇰', 'dai-loan': '🇹🇼',
                                                        'an-do': '🇮🇳', 'anh': '🇬🇧', 'phap': '🇫🇷', 'tay-ban-nha': '🇪🇸',
                                                        'duc': '🇩🇪', 'nga': '🇷🇺', 'y': '🇮🇹', 'mexico': '🇲🇽', 'uc': '🇦🇺'
                                                    };
                                                    countryCode = flagMap[slug] || '🏳️';
                                                }
                                            } catch(e){}
                                        }
                                        %>
                                        <span class="text-xl" title="Quốc gia"><%= countryCode %></span>
                                    </td>"""
    
    return status_td + tmdb_imdb + "\n" + type_td + country_code


content = re.sub(status_col_regex + r'\s*' + type_col_regex, replacer, content)

# Also fix the styling of the 4 cards at the top
card_style_old = 'bg-card border border-card rounded-xl p-8 flex flex-col hover:border-cyan-500/30 transition relative overflow-hidden group'
card_style_new = 'bg-card border border-card rounded-xl p-8 flex flex-col hover:border-cyan-500/30 transition relative overflow-hidden group shadow-xl'
# Actually let's inject a soft glow background blob in the cards
# Find '<div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition">'
glow_old = '<div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition">'
glow_new = '<div class="absolute -top-10 -right-10 w-40 h-40 bg-gradient-to-br from-white/5 to-transparent rounded-full blur-2xl opacity-0 group-hover:opacity-100 transition duration-700"></div>\n                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition scale-100 group-hover:scale-110 duration-500">'
content = content.replace(glow_old, glow_new)

with open('views/index.ejs', 'w', encoding='utf-8') as f:
    f.write(content)

