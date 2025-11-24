// resources/js/types/inertia.d.ts
// resources/js/types/inertia.d.ts

import '@inertiajs/core';

import { PageProps } from '@inertiajs/core';

export interface GenericPageProps extends PageProps {
    [key: string]: unknown;
}

declare module '@inertiajs/core' {
    export interface PageProps extends GenericPageProps {
        auth: {
            user: {
                id: number;
                name: string;
                email: string;
            } | null;
        };
    }
}
