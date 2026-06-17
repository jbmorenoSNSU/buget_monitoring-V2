import { usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { watch } from 'vue';

/**
 * Composable to show toast notifications from Inertia flash messages.
 */
export function useFlash() {
    const toast = useToast();
    const page = usePage();

    watch(
        () => page.props.flash,
        (flash: any) => {
            if (flash?.success) {
                toast.success(flash.success);
                flash.success = null;
            }
            if (flash?.error) {
                toast.error(flash.error);
                flash.error = null;
            }
        },
        { immediate: true, deep: true }
    );

    return { toast };
}
