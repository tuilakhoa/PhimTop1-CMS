import re

def optimize_swiper_sizes(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # History size
    content = content.replace("w-64 block", "w-[200px] sm:w-[240px] md:w-[280px] block")
    
    # AI Recommend & Ranking sizes
    content = content.replace("w-40 sm:w-48 block", "w-[130px] sm:w-[150px] md:w-[180px] block")
    content = content.replace("w-36 sm:w-44 block relative", "w-[130px] sm:w-[150px] md:w-[180px] block relative")
    
    # Inject CSS
    css_fix = """<style>
.swiper-button-next, .swiper-button-prev {
    color: white !important;
    background: rgba(0,0,0,0.6);
    border-radius: 50%;
    width: 36px !important;
    height: 36px !important;
    transition: all 0.3s ease;
    backdrop-filter: blur(4px);
}
.swiper-button-next:after, .swiper-button-prev:after {
    font-size: 16px !important;
    font-weight: 800;
}
.swiper-button-next:hover, .swiper-button-prev:hover {
    background: rgba(220, 38, 38, 0.9);
    transform: scale(1.1);
}
.swiper-button-disabled {
    opacity: 0 !important;
}
</style>
"""
    if "swiper-button-disabled" not in content:
        # Find a good place to inject, right after the swiper CSS link
        content = content.replace(
            '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />',
            '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />\n' + css_fix
        )

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

optimize_swiper_sizes('themes/dark/index.php')
