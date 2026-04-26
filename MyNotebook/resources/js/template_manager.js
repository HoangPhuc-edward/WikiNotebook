// ==========================================
// 1. KHỞI TẠO AN TOÀN TRONG MEDIAWIKI
// ==========================================
function initTemplateManager() {
    const listContainer = document.getElementById('template-list-container');
    if (!listContainer) return; // Thoát nếu không phải trang Template


    // Gắn sự kiện cho các nút
    document.getElementById('btn_add_section').addEventListener('click', () => addTemplateSection());
    document.getElementById('btn_save_template').addEventListener('click', handleSaveTemplate);
    document.getElementById('btn_delete_template').addEventListener('click', handleDeleteTemplate);
    document.getElementById('btn_cancel_template').addEventListener('click', cancelTemplateEdit);
    document.getElementById('btn_go_back').addEventListener('click', function() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = mw.util.getUrl('Special:MyNotebook');
    }
    });

    // Mặc định tạo ra 1 mục trống khi vừa vào trang
    addTemplateSection();
    
    // Tải danh sách
    loadTemplates();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initTemplateManager);
} else {
    initTemplateManager();
}

// ==========================================
// 2. HÀM QUẢN LÝ CÁC MỤC ĐỘNG (DYNAMIC FORM)
// ==========================================
function addTemplateSection(titleVal = '', descVal = '') {
    const container = document.getElementById('prompt_structure_container');
    
    // Tạo một khối bọc (Wrapper) cho 1 mục
    const sectionDiv = document.createElement('div');
    sectionDiv.className = 'template-section-item';
    sectionDiv.style.cssText = "background: #ffffff; border: 1px solid #cbd5e1; padding: 10px; border-radius: 6px; position: relative;";
    
    // Nút Xóa mục này
    const removeBtn = document.createElement('button');
    removeBtn.innerHTML = '✕';
    removeBtn.style.cssText = "position: absolute; top: 5px; right: 5px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; font-size: 10px; display: flex; align-items: center; justify-content: center;";
    removeBtn.onclick = function() {
        container.removeChild(sectionDiv);
    };

    // Nội dung: Tiêu đề và Miêu tả
    const contentHtml = `
        <div style="margin-bottom: 8px;">
            <input type="text" class="section-title" placeholder="Tiêu đề (VD: Giới thiệu chung)" value="${titleVal}" style="width: 90%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px;">
        </div>
        <div>
            <textarea class="section-desc" placeholder="Miêu tả nội dung AI cần viết..." rows="2" style="width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; resize: vertical;">${descVal}</textarea>
        </div>
    `;
    
    sectionDiv.innerHTML = contentHtml;
    sectionDiv.appendChild(removeBtn);
    container.appendChild(sectionDiv);
}

// Hàm quét toàn bộ form để gom thành JSON mảng
function getPromptStructureData() {
    const sections = document.querySelectorAll('.template-section-item');
    let structureArray = [];
    
    sections.forEach(section => {
        const title = section.querySelector('.section-title').value.trim();
        const desc = section.querySelector('.section-desc').value.trim();
        
        // Chỉ lấy những mục có điền tiêu đề
        if (title !== '') {
            structureArray.push({ title: title, description: desc });
        }
    });
    
    return structureArray;
}

// ==========================================
// 3. API VÀ LOGIC GIAO DIỆN (STATE)
// ==========================================

window.allTemplates = [];

// Biến toàn cục để lưu trữ danh sách gốc
window.allTemplates = [];

async function loadTemplates() {
    const listContainer = document.getElementById('template-list-container');
    listContainer.innerHTML = "<p style='color: #666;'><i class='fas fa-spinner fa-spin'></i> Đang tải dữ liệu...</p>";

    const userId = apiClient.getUserId();
    const data = await apiClient.apiGet(`/templates/${userId}`);

    if (!data) {
        listContainer.innerHTML = "<p style='color: red;'>Lỗi khi tải danh sách Template.</p>";
        return;
    }

    // Lưu dữ liệu vào biến toàn cục để phục vụ hàm filter sau này
    window.allTemplates = data; 
    
    // Gọi hàm render để hiển thị lần đầu
    renderTemplates(window.allTemplates);
}

// Hàm bổ trợ: Vẽ danh sách Template ra màn hình
function renderTemplates(templates) {
    const listContainer = document.getElementById('template-list-container');
    listContainer.innerHTML = "";

    if (templates.length === 0) {
        listContainer.innerHTML = "<p style='color: #64748b; font-style: italic; padding: 10px;'>Không tìm thấy template nào phù hợp.</p>";
        return;
    }

    templates.forEach(tpl => {
        const div = document.createElement('div');
        div.style.cssText = "border: 1px solid #e2e8f0; padding: 12px; margin-bottom: 10px; border-radius: 6px; cursor: pointer; background: #f8fafc; transition: background 0.2s;";
        
        div.onmouseover = () => div.style.background = "#e2e8f0";
        div.onmouseout = () => div.style.background = "#f8fafc";
        
        const sectionCount = Array.isArray(tpl.prompt_structure) ? tpl.prompt_structure.length : 0;

        div.innerHTML = `
            <strong style='font-size: 15px; color: #0f172a;'>${tpl.name}</strong><br>
            <span style='font-size: 13px; color: #64748b;'>Gồm ${sectionCount} mục nội dung</span>
        `;

        div.onclick = () => selectTemplate(tpl);
        listContainer.appendChild(div);
    });
}

// Hàm xử lý tìm kiếm (Kích hoạt mỗi khi gõ phím)
function filterTemplates() {
    const keyword = document.getElementById('search_input').value.toLowerCase().trim();
    
    // Lọc mảng dữ liệu dựa trên tên (name) của template
    const filtered = window.allTemplates.filter(tpl => 
        tpl.name.toLowerCase().includes(keyword)
    );

    // Vẽ lại danh sách đã lọc
    renderTemplates(filtered);
}

function selectTemplate(tpl) {
    document.getElementById('form_template_id').value = tpl.id;
    document.getElementById('form_template_name').value = tpl.name;

    // Xóa trắng danh sách mục hiện tại
    const container = document.getElementById('prompt_structure_container');
    container.innerHTML = '';

    // Đổ dữ liệu từ mảng JSON ra các Form nhỏ
    if (Array.isArray(tpl.prompt_structure) && tpl.prompt_structure.length > 0) {
        tpl.prompt_structure.forEach(item => {
            addTemplateSection(item.title, item.description);
        });
    } else {
        // Nếu không có mục nào, tạo 1 mục trống
        addTemplateSection();
    }

    // Đổi trạng thái giao diện sang "Sửa"
    document.getElementById('form-title').innerText = "Cập nhật Template: " + tpl.name;
    document.getElementById('btn_save_template').innerText = "Cập nhật";
    toggleTemplateButtons(false);
}

function cancelTemplateEdit() {
    document.getElementById('form_template_id').value = '';
    document.getElementById('form_template_name').value = '';

    // Trả lại 1 mục trống duy nhất
    const container = document.getElementById('prompt_structure_container');
    container.innerHTML = '';
    addTemplateSection();

    document.getElementById('form-title').innerText = "Thêm Template mới";
    document.getElementById('btn_save_template').innerText = "Thêm mới";
    toggleTemplateButtons(true);
}

function toggleTemplateButtons(isDisabled) {
    const btnDelete = document.getElementById('btn_delete_template');
    const btnCancel = document.getElementById('btn_cancel_template');
    
    btnDelete.disabled = isDisabled;
    btnCancel.disabled = isDisabled;
    
    btnDelete.style.opacity = isDisabled ? "0.5" : "1";
    btnDelete.style.cursor = isDisabled ? "not-allowed" : "pointer";
    btnCancel.style.opacity = isDisabled ? "0.5" : "1";
    btnCancel.style.cursor = isDisabled ? "not-allowed" : "pointer";
}

// ==========================================
// 4. LƯU (THÊM / SỬA)
// ==========================================
async function handleSaveTemplate() {
    const id = document.getElementById('form_template_id').value;
    const name = document.getElementById('form_template_name').value.trim();
    
    // Kêu gọi hàm gom dữ liệu JSON
    const structureData = getPromptStructureData();

    if (name === '') {
        alert("Vui lòng nhập Tên Template!");
        return;
    }

    if (structureData.length === 0) {
        alert("Vui lòng nhập ít nhất một mục Tiêu đề cho Template!");
        return;
    }

    const payload = {
        name: name,
        prompt_structure: structureData,
        user_id: apiClient.getUserId()
    };

    let result;
    if (id) {
        // API PUT (Sửa) - Tôi đang placeholder, bạn có thể gọi backend khi sẵn sàng
        result = await apiClient.apiPut(`/templates/${id}`, payload);
        if (!result) {
            alert("Lỗi khi cập nhật Template. Vui lòng thử lại.");
        }
        else {
            alert("Cập nhật thành công!");
        }
        return;
    } else {
        // API POST (Thêm mới)
        result = await apiClient.apiPost(`/templates`, payload);
    }

    if (result) {
        alert(id ? "Cập nhật thành công!" : "Thêm Template thành công!");
        cancelTemplateEdit(); 
        loadTemplates();   
    }
}

// Placeholder cho Xóa
async function handleDeleteTemplate() {
    const id = document.getElementById('form_template_id').value;
    if (!id) return;

    if (!confirm("Tính năng Xóa đang xây dựng ở Backend. Bạn có muốn gọi thử không?")) return;
    
    const result = await apiClient.apiDelete(`/templates/${id}`);
    if (result) { 
        alert("Xóa thành công!");
        cancelTemplateEdit(); loadTemplates(); }
}