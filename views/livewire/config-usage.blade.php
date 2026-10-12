<button
                            type="button"
                            class="{{ $usageClass }}"
                            style="{{ $usageStyle ?? 'cursor: pointer; text-align: left;' }}"
                            data-config-usage="{{ $usage }}"
                            x-data="{ copied: false, copyFailed: false }"
                            x-on:click="
                                copied = false;
                                copyFailed = false;
                                (navigator.clipboard
                                    ? navigator.clipboard.writeText($el.dataset.configUsage)
                                    : Promise.reject())
                                    .then(() => { copied = true; window.setTimeout(() => copied = false, 1600); })
                                    .catch(() => { copyFailed = true; });
                            "
                            x-bind:aria-pressed="copied"
                        >
                            <code>{{ $usage }}</code>
                            <span x-show="copied" x-cloak aria-hidden="true">✓</span>
                            <span x-show="copyFailed" x-cloak aria-hidden="true">!</span>
                        </button>
