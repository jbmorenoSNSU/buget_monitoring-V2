import { ref } from 'vue';

const pageTitle = ref('');

export function usePageTitle() {
    const setPageTitle = (title: string) => {
        pageTitle.value = title;
    };
    
    return {
        pageTitle,
        setPageTitle
    };
}
