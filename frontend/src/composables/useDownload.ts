import { saveAs } from 'file-saver';
import type { Settings } from '@/interfaces/Settings.ts';
import { ref } from 'vue';

const showDownloader = ref(false)

export function useDownload() {
    // see https://stackoverflow.com/a/67994693
    function _getFileNameFromContentDisposition(disposition: string | null): string {
        let fileName = 'download';
        if (disposition === null) {
            return fileName;
        }
        const utf8FilenameRegex = /filename\*=UTF-8''([\w%\-.]+)(?:; ?|$)/i;
        const asciiFilenameRegex = /filename=(["']?)(.*?[^\\])\1(?:; ?|$)/i;

        if (utf8FilenameRegex.test(disposition)) {
            const matched = utf8FilenameRegex.exec(disposition);
            if (matched !== null) {
                fileName = decodeURIComponent(matched[1]);
            }
        } else {
            const matches = asciiFilenameRegex.exec(disposition);
            if (matches !== null && matches[2]) {
                fileName = matches[2];
            }
        }
        return fileName;
    }

    async function triggerDownload(settings: Settings) {
        const url = import.meta.env.VITE_API_URL + '/download';
        showDownloader.value = true

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(settings),
            });

            const contentType = (res.headers.get('content-type') || '').toLowerCase();
            if (contentType.includes('application/json')) {
                const data = await res.json();
                if (data && typeof data.error === 'string') {
                    let msg = data.error;
                    if (typeof data.errors === 'object') {
                        for(const prop in data.errors) {
                            msg += "\n" + prop + ":";
                            for(const e in data.errors[prop]) {
                                msg += "\n" + data.errors[prop][e];
                            }
                        }
                    }
                    throw new Error(msg);
                }
                if (data.message) {
                    throw new Error(data.message);
                }
                throw new Error('Unexpected JSON result.');
            }

            if (!res.ok) {
                throw new Error(`Download Error ${res.status}`);
            }

            const blob = await res.blob();
            const filename = _getFileNameFromContentDisposition(
                res.headers.get('content-disposition'),
            );
            saveAs(blob, filename);
        } catch (err: unknown) {
            if (typeof err === 'object' && Object.prototype.hasOwnProperty.call(err, 'message') && (err as {message: string}).message !== '') {
                alert((err as {message: string}).message);
            } else if (typeof err === 'string') {
                alert(err);
            } else {
                console.error(err);
            }
        } finally {
            showDownloader.value = false
        }

        showDownloader.value = false
    }

    return { triggerDownload, showDownloader };
}
