// Enhanced status toggling with confirmation and feedback
document.querySelectorAll('.toggle-status-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        const action = formData.get('user_action');
        const userId = formData.get('user_id');
        const statusText = action === 'activate' ? 'activate' : 'disable';
        
        if (!confirm(`Are you sure you want to ${statusText} this user?`)) {
            return;
        }
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });
            
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                const result = await response.json();
                if (result.success) {
                    showToast(`User ${statusText}d successfully`, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(result.message || 'Operation failed', 'error');
                }
            }
        } catch (error) {
            showToast('Network error - please try again', 'error');
            console.error('Error:', error);
        }
    });
});

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }, 100);
}

document.getElementById('toggle-btn').addEventListener('click', function() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('open');
});
