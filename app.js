// app.js - สคริปต์ตัวอย่าง

const sampleData = [
  { id: 1, text: 'สินค้า: กาแฟ' },
  { id: 2, text: 'สินค้า: ขนม' },
  { id: 3, text: 'สินค้า: ชาเขียว' },
];

const listEl = document.getElementById('list');
const messageEl = document.getElementById('message');
const loadBtn = document.getElementById('loadBtn');
const clearBtn = document.getElementById('clearBtn');
const addBtn = document.getElementById('addBtn');
const newItemInput = document.getElementById('newItem');

let items = [];

function renderList() {
  listEl.innerHTML = '';
  if (items.length === 0) {
    listEl.innerHTML = '<li class="list-group-item text-muted">ไม่มีรายการ</li>';
    return;
  }
  items.forEach(item => {
    const li = document.createElement('li');
    li.className = 'list-group-item d-flex justify-content-between align-items-center';
    li.textContent = item.text;

    const delBtn = document.createElement('button');
    delBtn.className = 'btn btn-sm btn-outline-danger ms-2';
    delBtn.textContent = 'ลบ';
    delBtn.addEventListener('click', () => {
      items = items.filter(i => i.id !== item.id);
      renderList();
      setMessage('ลบรายการเรียบร้อย');
    });

    li.appendChild(delBtn);
    listEl.appendChild(li);
  });
}

function setMessage(text, type = 'info') {
  messageEl.className = `alert alert-${type}`;
  messageEl.textContent = text;
}

loadBtn.addEventListener('click', () => {
  setMessage('กำลังโหลดข้อมูล...');
  setTimeout(() => {
    items = sampleData.slice();
    renderList();
    setMessage('โหลดข้อมูลเรียบร้อย', 'success');
  }, 400);
});

clearBtn.addEventListener('click', () => {
  items = [];
  renderList();
  setMessage('ล้างรายการแล้ว', 'secondary');
});

addBtn.addEventListener('click', () => {
  const text = newItemInput.value.trim();
  if (!text) {
    setMessage('กรุณากรอกข้อความก่อน', 'warning');
    return;
  }
  const id = items.length ? Math.max(...items.map(i => i.id)) + 1 : 1;
  items.push({ id, text });
  newItemInput.value = '';
  renderList();
  setMessage('เพิ่มรายการเรียบร้อย', 'success');
});

newItemInput.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') addBtn.click();
});

renderList();
