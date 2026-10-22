/**
 * Generic Permission Interceptor
 * @param {HTMLElement} element - The clicked button or trigger
 * @param {Function} callback - Function to run if permission is granted
 */
function handleAction(element, callback) {
    const permission = parseInt(element.getAttribute('data-permission'));
    const actionName = element.getAttribute('data-action') || 'this action';

    if (permission === 1) {
        callback();
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Access Denied',
            text: `You do not have Acess to '${actionName}'.`,
            confirmButtonColor: '#d33',
            customClass: {
                popup: 'shadow-lg rounded-3'
            }
        });
    }
}