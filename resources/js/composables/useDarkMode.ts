/**
 * Composable para gerenciar dark mode
 * Persiste preferência no localStorage e aplica classe ao documento
 */

import { ref, watch, onMounted } from 'vue';

const STORAGE_KEY = 'app-theme-mode';
const THEME_DARK = 'dark';
const THEME_LIGHT = 'light';

// Estado reativo para o tema
const isDarkMode = ref(false);

/**
 * Detecta a preferência do sistema
 */
const getSystemTheme = (): 'dark' | 'light' => {
    if (typeof window === 'undefined') return THEME_LIGHT;

    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? THEME_DARK : THEME_LIGHT;
};

/**
 * Obtém o tema salvo no localStorage
 */
const getSavedTheme = (): 'dark' | 'light' | null => {
    if (typeof window === 'undefined') return null;

    return localStorage.getItem(STORAGE_KEY) as 'dark' | 'light' | null;
};

/**
 * Aplica o tema ao documento
 */
const applyTheme = (theme: 'dark' | 'light') => {
    if (typeof document === 'undefined') return;

    const html = document.documentElement;

    if (theme === THEME_DARK) {
        html.classList.add('dark');
        isDarkMode.value = true;
    } else {
        html.classList.remove('dark');
        isDarkMode.value = false;
    }

    // Salva no localStorage
    localStorage.setItem(STORAGE_KEY, theme);
};

/**
 * Inicializa o tema na primeira carga
 */
const initializeTheme = () => {
    if (typeof window === 'undefined') return;

    // Verifica se há tema salvo no localStorage
    const savedTheme = getSavedTheme();

    if (savedTheme) {
        applyTheme(savedTheme);
    } else {
        // Usa a preferência do sistema como padrão
        const systemTheme = getSystemTheme();
        applyTheme(systemTheme);
    }

    // Ouve mudanças na preferência do sistema
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const handleChange = (e: MediaQueryListEvent) => {
        const savedTheme = getSavedTheme();
        // Só aplica mudança do sistema se o usuário não tiver definido manualmente
        if (!savedTheme) {
            applyTheme(e.matches ? THEME_DARK : THEME_LIGHT);
        }
    };

    // Usa addEventListener se disponível (navegadores modernos)
    if (mediaQuery.addEventListener) {
        mediaQuery.addEventListener('change', handleChange);
    } else {
        // Fallback para addListener (navegadores mais antigos)
        mediaQuery.addListener(handleChange);
    }
};

/**
 * Alterna entre dark e light mode
 */
const toggleDarkMode = () => {
    applyTheme(isDarkMode.value ? THEME_LIGHT : THEME_DARK);
};

/**
 * Define o tema explicitamente
 */
const setTheme = (theme: 'dark' | 'light') => {
    applyTheme(theme);
};

/**
 * Hook principal para usar dark mode
 */
export const useDarkMode = () => {
    onMounted(() => {
        initializeTheme();
    });

    return {
        isDarkMode,
        toggleDarkMode,
        setTheme,
        theme: isDarkMode.value ? THEME_DARK : THEME_LIGHT,
    };
};

/**
 * Função para inicializar dark mode no setup da aplicação
 * Deve ser chamada no app.js antes de criar a app Vue
 */
export const setupDarkMode = () => {
    initializeTheme();
};
