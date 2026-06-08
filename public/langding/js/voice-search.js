$(document).ready(function () {
    // Biến toàn cục cho chức năng tìm kiếm xe
    let selectedManufacturer = '';
    let selectedModel = '';
    let selectedYear = '';

    // Kiểm tra trình duyệt có hỗ trợ voice không
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        $('.btn-search-icon.voice').hide(); // Ẩn nút voice nếu không hỗ trợ
    }

    // Khởi tạo voice recognition
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    let recognition;

    if (SpeechRecognition) {
        recognition = new SpeechRecognition();
        recognition.lang = 'vi-VN'; // Vietnamese for tire search
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;
        recognition.continuous = false;
    }

    let isListening = false;

    // Định nghĩa config
    const CONFIG = {
        ENDPOINTS: {
            // ✅ UNIFIED SEARCH ENDPOINT - Xử lý cả voice & text search
            SEARCH: '/api/search',  // Gộp chung 1 endpoint để dễ quản lý
        },
        DEFAULT_IMAGE: '/langding/imgs/product.png',
        MAX_DESCRIPTION_LENGTH: 100
    };

    /**
     * Tiền xử lý query cho kích thước lốp xe
     * @param {string} query - Query gốc
     * @return {string} - Query đã xử lý
     */
    function preprocessQuery(query) {
        if (!query) return '';

        const originalQuery = query.trim();

        // Mẫu kích thước lốp xe: XXX/YY RZZ (ví dụ: 265/65 R17)
        const tirePattern = /(\d{3})\/(\d{2})[\s]?R(\d{2})/i;

        // Biến thể query để tăng khả năng matching
        let queryVariations = [originalQuery];

        // Xử lý các trường hợp có dấu "/"
        if (originalQuery.includes('/')) {
            // Thêm phiên bản không có dấu "/" (265/65 R17 -> 265 65 R17)
            queryVariations.push(originalQuery.replace(/\//g, ' '));

            // Trường hợp kích thước lốp đầy đủ
            const match = originalQuery.match(tirePattern);
            if (match) {
                // Phân tách thành chiều rộng, tỉ lệ, đường kính
                const [fullMatch, width, ratio, diameter] = match;

                // Thêm các biến thể
                queryVariations.push(`${width} ${ratio} R${diameter}`);  // Không có dấu "/"
                queryVariations.push(`${width}/${ratio}R${diameter}`);   // Không có khoảng trắng
                queryVariations.push(`${width}/${ratio}% R${diameter}`); // Dùng ký hiệu %
                queryVariations.push(`${width} ${ratio} ${diameter}`);   // Không có chữ R

                // Đôi khi người dùng nói hoặc ghi không có chữ R
                const noRPattern = /(\d{3})\/(\d{2})[\s](\d{2})/i;
                const noRMatch = originalQuery.match(noRPattern);
                if (noRMatch) {
                    const [noRFullMatch, noRWidth, noRRatio, noRDiameter] = noRMatch;
                    queryVariations.push(`${noRWidth}/${noRRatio} R${noRDiameter}`); // Thêm chữ R
                }
            }
            // Trường hợp kích thước không đầy đủ (chỉ có XXX/YY)
            else {
                const partialPattern = /(\d{3})\/(\d{2})/i;
                const partialMatch = originalQuery.match(partialPattern);

                if (partialMatch) {
                    const [fullMatch, width, ratio] = partialMatch;
                    queryVariations.push(`${width} ${ratio}`); // Không dấu "/"
                }
            }
        }

        // Log để debug
        console.log('Query variations:', queryVariations);

        // Join các biến thể bằng OR để elasticsearch xử lý
        return queryVariations.join(' OR ');
    }

    // Khi click vào nút voice
    $('.btn-search-icon.voice').click(function (e) {
    e.preventDefault();

    if (!recognition) {
        alert('Trình duyệt không hỗ trợ nhận dạng giọng nói. Vui lòng sử dụng trình duyệt Chrome hoặc Edge.');
        return;
    }

    if (isListening) {
        // Đang nghe → Dừng lại
        recognition.stop();
        isListening = false;
        $('.btn-search-icon.voice').removeClass('listening');
        console.log('Đã dừng nhận dạng giọng nói');
    } else {
        // Chưa nghe → Bắt đầu nghe
        try {
            recognition.start();
            console.log('Bắt đầu nhận dạng giọng nói...');
        } catch (error) {
            console.error('Lỗi khi khởi động voice recognition:', error);
            
            // ✅ FIX: Xử lý lỗi cụ thể
            if (error.name === 'InvalidStateError') {
                alert('Nhận dạng giọng nói đang chạy. Vui lòng đợi...');
            } else if (error.name === 'NotAllowedError') {
                alert('Vui lòng cho phép truy cập microphone để sử dụng tính năng tìm kiếm bằng giọng nói.');
            } else {
                alert('Không thể khởi động nhận dạng giọng nói. Vui lòng thử lại.');
            }
        }
    }
});

    // Chỉ setup events nếu recognition được hỗ trợ
    if (recognition) {
        // Khi bắt đầu nghe
        recognition.onstart = function () {
            isListening = true;
            $('.btn-search-icon.voice').addClass('listening');
            console.log('Đang nghe...');
        };

        // Khi có kết quả
        recognition.onresult = function (event) {
    const lastResult = event.results.length - 1;
    const transcript = event.results[lastResult][0].transcript;
    const confidence = event.results[lastResult][0].confidence;

    console.log('Bạn nói:', transcript, 'Độ tin cậy:', confidence);

    // ✅ FIX: Tìm input với selector chính xác hơn
    const $searchInput = $('.search-widget-input, .search-widget input[type="text"], input.search-widget-input');
    if ($searchInput.length > 0) {
        $searchInput.first().val(transcript);
    } else {
        console.warn('⚠️ Không tìm thấy input search');
    }

    // Gọi hàm chung cho search
    performSearch(transcript, 'voice', confidence);
};

        // Khi kết thúc
        recognition.onspeechend = function () {
            recognition.stop();
        };

        recognition.onend = function () {
            isListening = false;
            $('.btn-search-icon.voice').removeClass('listening');
            console.log('Kết thúc nghe');
        };

        // Khi có lỗi
        recognition.onerror = function (event) {
    console.error('Lỗi voice recognition:', event.error);
    isListening = false;
    $('.btn-search-icon.voice').removeClass('listening');

    // ✅ FIX: Thông báo lỗi rõ ràng hơn
    let errorMessage = 'Có lỗi xảy ra khi nhận dạng giọng nói.';
    
    switch (event.error) {
        case 'no-speech':
            errorMessage = 'Không nghe thấy tiếng nói. Vui lòng nói lại hoặc kiểm tra microphone.';
            break;
        case 'audio-capture':
            errorMessage = 'Không tìm thấy microphone. Vui lòng kiểm tra thiết bị của bạn.';
            break;
        case 'not-allowed':
            errorMessage = 'Bạn đã từ chối quyền truy cập microphone. Vui lòng cấp quyền trong cài đặt trình duyệt.';
            break;
        case 'network':
            errorMessage = 'Lỗi kết nối mạng. Vui lòng kiểm tra kết nối internet.';
            break;
        case 'aborted':
            errorMessage = 'Nhận dạng giọng nói đã bị hủy.';
            break;
        default:
            errorMessage = `Lỗi: ${event.error}. Vui lòng thử lại.`;
    }
    
    // Chỉ hiển thị alert nếu không phải lỗi "aborted" (người dùng tự dừng)
    if (event.error !== 'aborted') {
        alert(errorMessage);
    }
};
    }

    /**
     * Hiển thị loading trong giao diện
     */
    function showLoading() {
        // Tạo hoặc lấy loading indicator
        let loadingEl = document.getElementById('search-loading-indicator');

        if (!loadingEl) {
            loadingEl = document.createElement('div');
            loadingEl.id = 'search-loading-indicator';
            loadingEl.innerHTML = `
                <div class="search-loading-overlay">
                    <div class="search-loading-spinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                        <p class="mt-2">Đang tìm kiếm...</p>
                    </div>
                </div>
            `;
            document.body.appendChild(loadingEl);

            // Thêm style cho loading overlay
            const style = document.createElement('style');
            style.textContent = `
                .search-loading-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0,0,0,0.5);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                }
                .search-loading-spinner {
                    background-color: white;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 0 15px rgba(0,0,0,0.2);
                    text-align: center;
                }
            `;
            document.head.appendChild(style);
        }

        loadingEl.style.display = 'block';
    }

    /**
     * Ẩn loading indicator
     */
    function hideLoading() {
        const loadingEl = document.getElementById('search-loading-indicator');
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
    }

    /**
     * Hàm tìm kiếm chung cho cả voice và text search
     * @param {string} query - Từ khóa tìm kiếm
     * @param {string} searchType - Loại tìm kiếm ('voice' hoặc 'text')
     * @param {number} confidence - Độ tin cậy của nhận dạng giọng nói (chỉ cho voice search)
     */
    async function performSearch(query, searchType = 'text', confidence = 1.0) {
    try {
        if (!query || query.trim() === '') {
            alert('Vui lòng nhập từ khóa tìm kiếm');
            return;
        }
        
        // Hiển thị loading trong giao diện
        showLoading();

        // Tiền xử lý query để tăng khả năng match
        const processedQuery = preprocessQuery(query);

        // ✅ FIX: Lấy CSRF token với error handling
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        
        if (!csrfToken) {
            console.warn('⚠️ CSRF token not found, request may fail');
        }

        // ✅ FIX: Lấy vehicleType từ global variable
        const vehicleType = window.currentVehicleType || 'oto';

        console.log('Sending search request:', {
            query,
            processedQuery,
            confidence,
            type: searchType,
            vehicleType: vehicleType
        });

        // ✅ FIX: Gọi API search với vehicleType
        const response = await fetch(CONFIG.ENDPOINTS.SEARCH, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                query: query,  // ✅ Dùng query gốc, không dùng processedQuery (cho voice/text)
                type: searchType,
                confidence: confidence,
                vehicleType: vehicleType  // ✅ THÊM vehicleType
            }),
        });
        
        // Kiểm tra và log response status
        console.log('Search response status:', response.status);

        // Nếu response không OK, cố gắng lấy thêm thông tin chi tiết
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Server response:', errorText);
            throw new Error(`Search request failed with status ${response.status}: ${errorText}`);
        }

        // Parse response JSON
        const data = await response.json();

            // Log data để debug
            console.log('🔍 ========== SEARCH DEBUG ==========');
            console.log('📝 Query:', query);
            console.log('🎯 Search Type:', searchType);
            console.log('📊 API Response:', data);
            console.log('✅ Success:', data.success);
            console.log('📦 Results Count:', data.results ? data.results.length : 0);
            console.log('====================================');

            // // ⚠️ DEBUG: Dừng lại nếu không có kết quả
            // if (!data.results || data.results.length === 0) {
            //     hideLoading();
            //     console.error('❌ NO RESULTS FOUND!');
            //     console.error('Query sent:', query);
            //     console.error('Search type:', searchType);
            //     console.error('Full response:', JSON.stringify(data, null, 2));

            //     alert('⚠️ DEBUG MODE\n\n' +
            //           'Không tìm thấy kết quả!\n\n' +
            //           'Query: ' + query + '\n' +
            //           'Type: ' + searchType + '\n\n' +
            //           'Mở Console (F12) để xem chi tiết.');

            //     // DỪNG LẠI - không redirect
            //     return;
            // }

            console.log('Search results type:', typeof data);
            // Đảm bảo dữ liệu được lưu trữ là một chuỗi JSON hợp lệ
            let searchResultsToStore;
            if (typeof data === 'object') {
                searchResultsToStore = JSON.stringify(data);
            } else if (typeof data === 'string') {
                // Kiểm tra xem string có phải là JSON hợp lệ không
                try {
                    JSON.parse(data);
                    searchResultsToStore = data;
                } catch (e) {
                    searchResultsToStore = JSON.stringify({ result: data });
                }
            } else {
                searchResultsToStore = JSON.stringify({ result: data });
            }
            // Ẩn loading
            hideLoading();

            // Lưu kết quả vào session storage
            sessionStorage.setItem('voiceSearchResults', searchResultsToStore);
            sessionStorage.setItem('voiceSearchQuery', query);

            // ✅ FIX: Lấy locale hiện tại từ URL
            window.location.href = '/category-search?search=' + encodeURIComponent(query) + '&type=' + searchType;
        } catch (error) {
            // Ghi log lỗi chi tiết vào console để debug
            console.error('Error during search:', error);

            // Xác định loại lỗi và hiển thị thông báo phù hợp
            let userMessage = 'Đã xảy ra lỗi khi tìm kiếm. Vui lòng thử lại sau.';
            let errorCode = 'general_error';

            // Kiểm tra lỗi cụ thể và đưa ra thông báo phù hợp
            if (error.message.includes('503')) {
                userMessage = 'Hệ thống tìm kiếm tạm thời không khả dụng. Vui lòng thử lại sau.';
                errorCode = 'service_unavailable';
            } else if (error.message.includes('404')) {
                userMessage = 'Không tìm thấy dịch vụ tìm kiếm. Vui lòng liên hệ quản trị viên.';
                errorCode = 'service_not_found';
            } else if (error.message.includes('timeout') || error.message.includes('network')) {
                userMessage = 'Lỗi kết nối mạng. Vui lòng kiểm tra kết nối của bạn và thử lại.';
                errorCode = 'network_error';
            }

            // Ẩn loading
            hideLoading();

            // Lưu thông tin lỗi vào session storage để có thể hiển thị trên trang tìm kiếm
            sessionStorage.setItem('searchError', JSON.stringify({
                message: userMessage,
                code: errorCode,
                query: query
            }));

            // ✅ FIX: Chuyển hướng với locale prefix
            const currentLocale = window.location.pathname.split('/')[1] || 'vi';
            window.location.href = '/category-search?search=' + encodeURIComponent(query) + '&type=' + searchType + '&error=' + errorCode;
        }
    }


    // ===== CHỨC NĂNG TÌM KIẾM VĂN BẢN =====

    // Thêm chức năng click cho text search button
    $('.search-widget-input').on('keypress', function (e) {
        if (e.which === 13) { // Enter key
            e.preventDefault(); // Ngăn form submit mặc định
            const query = $(this).val().trim();
            if (query) {
                performSearch(query, 'text');
            }
        }
    });

    // Thêm button search cho text input
    $('.btn-search-icon.search').on('click', function () {
        const query = $('.search-widget-input').val().trim();
        if (query) {
            performSearch(query, 'text');
        }
    });

    // ===== CHỨC NĂNG TÌM KIẾM XE =====

    // Xử lý khi chọn nhà sản xuất (manufacturer)
    $(document).on('click', '.search-widget-popup .search-widget-list[data-step-target="1"] .manufacturer-item', function () {
        selectedManufacturer = $(this).text();
        const manufacturer = $(this).text();

        // Hiển thị các model tương ứng
        const models = carModels[manufacturer] || [];
        let modelsHtml = '';
        if (models.length > 0) {
            models.forEach(model => {
                modelsHtml += `<div class="search-widget-popup-item model-item" data-value="${model}">${model}</div>`;
            });
        } else {
            modelsHtml = '<div class="alert alert-info">Không có mẫu xe nào cho thương hiệu này</div>';
        }
        $('#manufacturer-models-container').html(modelsHtml);

        // Ẩn step 1, hiện step 2
        $(".search-widget-popup .search-widget-list").addClass("d-none");
        $(".search-widget-popup .search-widget-list[data-step-target=2]").removeClass("d-none");

        // Cập nhật title active
        $(".search-widget-popup .search-widget-popup-title").removeClass("active");
        $(".search-widget-popup .search-widget-popup-title[data-step='2']").addClass("active");
    });

    // Xử lý khi chọn mẫu xe (model)
    $(document).on('click', '.search-widget-popup .search-widget-list[data-step-target="2"] .model-item', function () {
        selectedModel = $(this).text();
        const model = $(this).text();

        // Hiển thị các năm tương ứng
        const years = carYears[selectedManufacturer] && carYears[selectedManufacturer][model] || [];
        let yearsHtml = '<div class="search-widget-popup-item year-item" data-value="Tất cả">Tất cả</div>';
        if (years.length > 0) {
            years.forEach(year => {
                yearsHtml += `<div class="search-widget-popup-item year-item" data-value="${year}">${year}</div>`;
            });
        } else {
            yearsHtml = '<div class="alert alert-info">Không có thông tin năm sản xuất cho mẫu xe này</div>';
        }
        $('#model-years-container').html(yearsHtml);

        // Ẩn step 2, hiện step 3
        $(".search-widget-popup .search-widget-list").addClass("d-none");
        $(".search-widget-popup .search-widget-list[data-step-target=3]").removeClass("d-none");

        // Cập nhật title active
        $(".search-widget-popup .search-widget-popup-title").removeClass("active");
        $(".search-widget-popup .search-widget-popup-title[data-step='3']").addClass("active");
    });

    // Xử lý khi chọn năm sản xuất (year) - TỰ ĐỘNG SEARCH
    $(document).on('click', '.search-widget-popup .search-widget-list[data-step-target="3"] .year-item', function () {
        selectedYear = $(this).text();

        // Cập nhật giá trị vào ô input
        const searchQuery = `Thương hiệu: ${selectedManufacturer}, Mẫu xe: ${selectedModel}, Năm: ${selectedYear}`;
        $(".search-widget .search-widget-input").val(searchQuery);

        // Hiển thị nút đặt lại
        $(".search-widget .btn-search-icon-custom").addClass("d-none");
        $(".search-widget .btn-search-icon-custom-reset").removeClass("d-none");

        // Đóng modal
        if (bootstrap && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getInstance('#search-widget-type');
            if (modalInstance) {
                modalInstance.hide();
            }
        }

        // TỰ ĐỘNG THỰC HIỆN TÌM KIẾM
        performSearch(searchQuery, 'car');
    });

    // Xử lý tương tự cho kích cỡ lốp xe - TỰ ĐỘNG SEARCH
    $(document).on('click', '.search-widget-popup .search-widget-list[data-step-target="3"] .search-widget-popup-item', function () {
        if ($(this).closest('#search-widget-size').length > 0) {
            const sizeModal = $('#search-widget-size');
            const storedWidth = (sizeModal.data('selected-width') || '').toString().trim();
            const storedRate = (sizeModal.data('selected-rate') || '').toString().trim();
            let storedDiameter = (sizeModal.data('selected-diameter') || '').toString().trim();

            if (!storedDiameter) {
                storedDiameter = ($(this).attr('data-value') || $(this).text() || '').trim();
                sizeModal.data('selected-diameter', storedDiameter);
            }

            let searchQuery = $(".search-widget .search-widget-input").val().trim();

            if (!searchQuery && storedWidth && storedRate && storedDiameter) {
                searchQuery = `Độ rộng: ${storedWidth}, Tỷ lệ: ${storedRate}, Đường kính mâm xe: ${storedDiameter}`;
            }

            const normalizedQuery = (searchQuery || '').normalize('NFC');
            const widthValue = (storedWidth || normalizedQuery.match(/Độ\s*rộng:\s*([^,]+)/i)?.[1] || '').trim();
            const rateValue = (storedRate || normalizedQuery.match(/Tỷ\s*lệ:\s*([^,]+)/i)?.[1] || '').trim();
            const diameterValue = (storedDiameter || normalizedQuery.match(/Đường\s*kính\s*mâm\s*xe:\s*([^,]+)/i)?.[1] || '').trim();

            const invalidValues = [widthValue, rateValue, diameterValue].some(value =>
                !value ||
                value === '-' ||
                value.includes('...') ||
                /THƯƠNG HIỆU|ĐỘ RỘNG|TỶ LỆ|ĐƯỜNG KÍNH/i.test(value)
            );

            if (!widthValue || !rateValue || !diameterValue || invalidValues) {
                console.error('❌ Invalid size search query:', {
                    searchQuery,
                    widthValue,
                    rateValue,
                    diameterValue
                });
                alert('Vui lòng chọn đầy đủ thông số tìm kiếm');
                return;
            }

            const finalQuery = `Độ rộng: ${widthValue}, Tỷ lệ: ${rateValue}, Đường kính mâm xe: ${diameterValue}`;
            $(".search-widget .search-widget-input").val(finalQuery);

            // Hiển thị nút đặt lại
            $(".search-widget .btn-search-icon-custom").addClass("d-none");
            $(".search-widget .btn-search-icon-custom-reset").removeClass("d-none");

            // Đóng modal
            if (bootstrap && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getInstance('#search-widget-size');
                if (modalInstance) {
                    modalInstance.hide();
                }
            }

            // TỰ ĐỘNG THỰC HIỆN TÌM KIẾM
            performSearch(finalQuery, 'size');
        }
    });

    // Xử lý nút đặt lại
    $(".search-widget .btn-search-icon-custom-reset").click(function (e) {
        e.preventDefault(); // Ngăn chặn hành vi mặc định của nút

        // Reset các giá trị đã chọn
        selectedManufacturer = '';
        selectedModel = '';
        selectedYear = '';

        // Xóa nội dung ô input
        $(".search-widget .search-widget-input").val("");

        // Hiển thị lại nút tìm kiếm, ẩn nút đặt lại
        $(".search-widget .btn-search-icon-custom").removeClass("d-none");
        $(".search-widget .btn-search-icon-custom-reset").addClass("d-none");
    });

    // Xử lý nút "Quay lại"
    $(document).on('click', '.search-widget-popup-back', function () {
        const currentStep = parseInt($(this).closest('.search-widget-list').attr('data-step-target'));
        if (currentStep === 3) {
            // Reset giá trị năm đã chọn
            selectedYear = '';

            // Quay lại chọn model
            $('.search-widget-popup-title[data-step="3"]').removeClass('active');
            $('.search-widget-popup-title[data-step="2"]').addClass('active');
            $('.search-widget-list[data-step-target="3"]').addClass('d-none');
            $('.search-widget-list[data-step-target="2"]').removeClass('d-none');
        } else if (currentStep === 2) {
            // Reset giá trị model đã chọn
            selectedModel = '';

            // Quay lại chọn thương hiệu
            $('.search-widget-popup-title[data-step="2"]').removeClass('active');
            $('.search-widget-popup-title[data-step="1"]').addClass('active');
            $('.search-widget-list[data-step-target="2"]').addClass('d-none');
            $('.search-widget-list[data-step-target="1"]').removeClass('d-none');
        }
    });

    // Xử lý khi đóng modal
    $('.search-widget-popup').on('hidden.bs.modal', function () {
        // Reset các step khi đóng modal nếu chưa hoàn thành quy trình chọn
        if (!selectedYear) {
            $('.search-widget-popup-title').removeClass('active');
            $('.search-widget-popup-title[data-step="1"]').addClass('active');
            $('.search-widget-list').addClass('d-none');
            $('.search-widget-list[data-step-target="1"]').removeClass('d-none');
        }
    });

    // Export functions to global scope
    window.performSearch = performSearch;
});

//xử lý hiển thị kết quả tìm kiếm trên trang category-search
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const errorCode = urlParams.get('error');
    const searchQuery = urlParams.get('search');
    $('.search-widget input[type="text"]').val(searchQuery);
    if (errorCode) {
        // Xử lý hiển thị lỗi trong modal popup
        const searchError = JSON.parse(sessionStorage.getItem('searchError') || '{}');

        const modalHTML = `
                    <div class="modal fade" id="searchErrorModal" tabindex="-1" aria-labelledby="searchErrorModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-warning">
                                    <h5 class="modal-title" id="searchErrorModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Lỗi tìm kiếm</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-2">${searchError.message || 'Đã xảy ra lỗi khi tìm kiếm. Vui lòng thử lại sau.'}</p>
                                    <p class="mb-0 text-secondary"><strong>Từ khóa:</strong> "${searchError.query || urlParams.get('search')}"</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        const errorModal = new bootstrap.Modal(document.getElementById('searchErrorModal'));
        errorModal.show();
        sessionStorage.removeItem('searchError');
    }
    // 2. Xử lý hiển thị kết quả tìm kiếm thành công
    else if (searchQuery) {
        try {
            // Lấy kết quả tìm kiếm từ session storage
            const resultsJson = sessionStorage.getItem('voiceSearchResults');
            const query = sessionStorage.getItem('voiceSearchQuery') || searchQuery;
            // console.log('Kết quả tìm kiếm từ sessionStorage:', resultsJson);
            if (resultsJson) {
                const results = JSON.parse(resultsJson);
                const searchTitleElement = document.querySelector('.category-search-list h1');
                if (searchTitleElement) {
                    searchTitleElement.textContent = `Tìm thấy ${results.total || 0} sản phẩm`;
                }

                // Lấy container để hiển thị sản phẩm
                const searchResultsContainer = document.getElementById('search-results-container');
                if (searchResultsContainer) {
                    // Xác định mảng sản phẩm để hiển thị
                    let productsToShow = [];

                    // Kiểm tra cấu trúc của kết quả JSON
                    productsToShow = results.results;
                    // Kiểm tra nếu không có sản phẩm nào
                    if (results.total === 0) {
                        searchResultsContainer.innerHTML = `
                                    <div class="col-12 py-5">
                                        <div class="text-center empty-search-results p-5 rounded-4 shadow-sm">
                                            <div class="empty-search-icon mb-4">
                                                <i class="bi bi-search fs-1 text-secondary"></i>
                                            </div>
                                            <h3 class="fw-bold fs-4 mb-3">Không tìm thấy sản phẩm phù hợp</h3>
                                            <p class="text-secondary fs-5 mb-4">Không tìm thấy sản phẩm nào phù hợp với từ khóa "<strong>${query}</strong>"</p>
                                            <div class="suggestions mt-4">
                                                <h5 class="mb-3">Gợi ý:</h5>
                                                <ul class="list-unstyled text-start d-inline-block">
                                                    <li class="mb-2"><i class="bi bi-check-circle me-2 text-success"></i>Kiểm tra lỗi chính tả</li>
                                                    <li class="mb-2"><i class="bi bi-check-circle me-2 text-success"></i>Sử dụng từ khóa ngắn gọn hơn</li>
                                                    <li class="mb-2"><i class="bi bi-check-circle me-2 text-success"></i>Thử tìm kiếm với từ khóa khác</li>
                                                    <li><i class="bi bi-check-circle me-2 text-success"></i>Sử dụng bộ lọc sản phẩm</li>
                                                </ul>
                                            </div>
                                            <div class="mt-4">
                                                <a href="/" class="btn btn-primary btn-lg px-4 py-2 me-2">Về trang chủ</a>
                                                <button onclick="document.querySelector('.btn-search-icon.voice').click()" class="btn btn-outline-secondary btn-lg px-4 py-2">
                                                    <i class="bi bi-mic me-2"></i>Tìm bằng giọng nói
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `;
                        // Thêm style cho thông báo trống
                        const styleElement = document.createElement('style');
                        styleElement.textContent = `
                                    .empty-search-results {
                                        background-color: #f8f9fa;
                                        border: 1px solid #e9ecef;
                                    }
                                    .empty-search-icon {
                                        background-color: #e9ecef;
                                        width: 80px;
                                        height: 80px;
                                        border-radius: 50%;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        margin: 0 auto;
                                    }
                                    .btn-primary {
                                        background-color: #e31837;
                                        border-color: #e31837;
                                    }
                                    .btn-primary:hover {
                                        background-color: #c71530;
                                        border-color: #c71530;
                                    }
                                `;
                        document.head.appendChild(styleElement);
                    } else {
                        // Xóa nội dung cũ
                        searchResultsContainer.innerHTML = '';

                        // Hiển thị từng sản phẩm
                        productsToShow.forEach(product => {

                            // Tạo phần tử sản phẩm mới
                            const productElement = document.createElement('div');
                            productElement.className = 'col-md-6 col-xl-4 col-xxl-3';

                            // Tạo HTML cho sản phẩm
                            productElement.innerHTML = `
                                            <div class="product-item position-relative">
                                                <div class="product-attr">
                                                    <span class="product-new">NEW</span>
                                                    <span class="product-sale">-50%</span>
                                                </div>
                                                <div class="product-favourite">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="black"
                                                        class="bi bi-heart" viewBox="0 0 16 16">
                                                        <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15" />
                                                    </svg>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                                                        fill="currentColor" class="bi bi-heart-fill" viewBox="0 0 16 16">
                                                        <path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314" />
                                                    </svg>
                                                </div>
                                                <div class="product-item-img bg-img-contain ratio-1-1"
                                                    style="background-image: url('${product.tire.image || '/images/no-image.png'}');"></div>
                                                <div class="product-item-line"></div>
                                                <div class="product-item-category d-flex align-items-center gap-2">
                                                    <a href="">${product.tire.manufacturer || 'CASUMINA'}</a>
                                                    <div class="product-item-star text-nowrap">
                                                        <i class="bi bi-star-fill"></i>
                                                        <i class="bi bi-star-fill"></i>
                                                        <i class="bi bi-star-fill"></i>
                                                        <i class="bi bi-star-fill"></i>
                                                        <i class="bi bi-star-fill"></i>
                                                    </div>
                                                </div>
                                                <h3 class="product-item-title text-uppercase text-red fs-16 font-hanzel line-2 mt-2 mb-3">
                                                    ${product.tire.name || 'Sản phẩm'}
                                                </h3>
                                                <div class="fs-12 text-uppercase">${product.tire.size || `${product.tire.wide}/${product.tire.rate}R${product.tire.diameter}`}</div>
                                                <div class="product-price d-flex gap-3 align-items-center">
                                                    <span class="fs-24 font-hanzel text-red">${formatPrice(product.tire.price)}</span>
                                                    <span class="fs-14 text-red">Đã tính VAT</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-2">
                                                    <div class="product-item-view">
                                                        <a class="fs-16 text-black" href="/product/${product.tire.sku || product.tire.id}">CHI TIẾT</a>
                                                    </div>
                                                    <div class="cat-link d-flex align-items-center">
                                                        <a href="#" class="w-100" data-product-id="${product.tire.id}">
                                                            <img src="{{ asset('langding/imgs/category/cart-icon.svg') }}">
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        `;

                            // Thêm phần tử sản phẩm vào container
                            searchResultsContainer.appendChild(productElement);
                        }
                        );
                    }
                }
            }
        } catch (error) {
            console.error('Lỗi khi hiển thị kết quả tìm kiếm:', error);
        }
    }


    function formatPrice(price) {
        let numPrice = parseFloat(price);
        if (isNaN(numPrice)) return 'Liên hệ';
        return numPrice.toLocaleString('vi-VN') + 'đ';
    }
});
