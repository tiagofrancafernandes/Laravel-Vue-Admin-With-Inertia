import { ref, watch } from 'vue';

const STORAGE_KEY = 'stay_on_sales_page_preference';

export const useStaySalesPage = () => {
    const stayOnPage = ref<boolean>(getSavedPreference());

    // Watch for changes and persist to localStorage
    watch(stayOnPage, (newValue) => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(newValue));
    });

    function getSavedPreference(): boolean {
        if (typeof window === 'undefined') return false;

        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            return saved ? JSON.parse(saved) : false;
        } catch {
            return false;
        }
    }

    function togglePreference(): void {
        stayOnPage.value = !stayOnPage.value;
    }

    return {
        stayOnPage,
        togglePreference,
    };
};
