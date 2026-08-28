import re

file_path = 'themes/dark/index.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace <div class="swiper swiper-list w-full pb-4">
# with <div class="swiper swiper-list w-full"><div class="swiper-wrapper pb-4">
content = re.sub(
    r'<div class="swiper swiper-list w-full pb-4"(.*?)>',
    r'<div class="swiper swiper-list w-full pb-4" \1>\n<div class="swiper-wrapper">',
    content
)
content = re.sub(
    r'<div class="swiper swiper-list w-full pb-6"(.*?)>',
    r'<div class="swiper swiper-list w-full pb-6" \1>\n<div class="swiper-wrapper">',
    content
)

# After each loop or end of list, close swiper-wrapper
# For history:
content = content.replace('<?php endforeach; ?>\n                </div>\n            </div>\n            <?php endif; ?>', '<?php endforeach; ?>\n                </div>\n                <div class="swiper-pagination"></div>\n                <div class="swiper-button-prev"></div><div class="swiper-button-next"></div>\n            </div>\n            </div>\n            <?php endif; ?>')

# For ai-recommend-list, we do it in JS, but wait, the skeleton is PHP.
# The container is closed by </div>, but ai-recommend-list has id.
content = content.replace('id="ai-recommend-list">\n                    <!-- Skeleton Loader to prevent layout shift -->', '>\n<div class="swiper-wrapper" id="ai-recommend-list">\n                    <!-- Skeleton Loader to prevent layout shift -->')

content = content.replace('<?php endfor; ?>\n                </div>\n            </div>', '<?php endfor; ?>\n                </div>\n                <div class="swiper-button-prev"></div><div class="swiper-button-next"></div>\n            </div>\n            </div>')

# For ranking list
content = content.replace('<?php $rank++; endforeach; ?>\n                </div>\n            </div>', '<?php $rank++; endforeach; ?>\n                </div>\n                <div class="swiper-button-prev"></div><div class="swiper-button-next"></div>\n            </div>\n            </div>')

# Add swiper-slide to items
content = re.sub(r'<a href="(.*?)" class="group shrink-0 (.*?) block(.*?)">', r'<a href="\1" class="swiper-slide group shrink-0 \2 block\3">', content)
content = re.sub(r'<div class="group shrink-0 (.*?) block(.*?)">', r'<div class="swiper-slide group shrink-0 \1 block\2">', content)

# Update the JS for ai-recommend
content = content.replace('<a href="/phim/${item.slug}" class="group shrink-0', '<a href="/phim/${item.slug}" class="swiper-slide group shrink-0')

# Initialize swiper-list
init_js = """
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof Swiper !== 'undefined') {
                        document.querySelectorAll('.swiper-list').forEach(function(el) {
                            new Swiper(el, {
                                slidesPerView: 'auto',
                                spaceBetween: 16,
                                freeMode: true,
                                navigation: {
                                    nextEl: el.querySelector('.swiper-button-next'),
                                    prevEl: el.querySelector('.swiper-button-prev'),
                                },
                            });
                        });
                    }
                });
            </script>
"""
content = content.replace('</script>\n        </div>\n        \n    </div>\n</div>\n\n<?php include __DIR__ . \'/footer.php\'; ?>', '</script>\n        </div>\n        \n    </div>\n</div>\n' + init_js + '\n<?php include __DIR__ . \'/footer.php\'; ?>')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
