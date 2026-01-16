// Basic Sidebar Toggle
const menuToggle = document.getElementById('menu-toggle');
const sidebar = document.querySelector('.sidebar');

if(menuToggle) {
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });
}

// Invoice Generation
function downloadInvoice(invoiceId) {
    const invoiceElement = document.getElementById(invoiceId);
    if (!invoiceElement) return;

    html2canvas(invoiceElement).then(canvas => {
        const link = document.createElement('a');
        link.download = `invoice_${new Date().getTime()}.jpg`;
        link.href = canvas.toDataURL('image/jpeg', 0.9);
        link.click();
    });
}
