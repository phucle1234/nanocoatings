# 360° Car Viewer — Hướng dẫn nhúng nhanh

1) Chép 2 file này vào web của bạn:
   - `360-viewer.js`
   - (tùy chọn) dùng luôn `demo-embed.html` để kiểm thử.

2) Dùng ảnh mẫu online (chạy ngay):
   ```html
   <script src="./360-viewer.js" defer></script>
   <div class="spin360"
        data-total="73"
        data-template="https://scaleflex.cloudimg.io/v7/demo/suv-orange-car-360/orange-{i}.jpg"
        data-autoplay="true"
        data-speed="0.6"
        style="width:100%;max-width:1100px;aspect-ratio:16/9"></div>
   ```

3) Dùng ảnh của bạn (tự host):
   - Lưu 36 ảnh tại `/assets/car360/` với format `car_0001.png` → `car_0036.png`
   - Nhúng:
   ```html
   <script src="/js/360-viewer.js" defer></script>
   <div class="spin360"
        data-total="36"
        data-folder="/assets/car360"
        data-prefix="car_"
        data-pad="4"
        data-ext="png"
        data-autoplay="true"
        data-speed="0.6"
        style="width:100%;max-width:1100px;aspect-ratio:16/9"></div>
   ```

4) Mẹo tối ưu:
   - 36–72 khung là ngọt; nén ảnh ~150–250 KB/khung (WebP/AVIF càng tốt).
   - Để viewer hiển thị ổn định, đặt `aspect-ratio` theo ảnh gốc (ví dụ 16/9).
   - Có thể lazy-load ở dưới màn hình bằng `loading="lazy"` nếu bạn muốn mở rộng.

