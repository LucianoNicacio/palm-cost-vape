export default {
    update: {
        form: () => ({
            action: '/user/profile-information',
            method: 'put',
        }),
    },
    destroy: {
        form: () => ({
            action: '/user',
            method: 'delete',
        }),
    },
};