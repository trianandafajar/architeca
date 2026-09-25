<div>
    <script>
        document.addEventListener('livewire:init', () => {
            const storageKey = '{{ $storageKey }}';
            Livewire.hook('component.init', ({ component }) => {
                const state = component.ephemeral ?? component.canonical ?? null;
                if (!state || !('data' in state)) {
                    return;
                }

                const savedData = localStorage.getItem(storageKey);
                if (!savedData) {
                    return;
                }

                try {
                    const parsed = JSON.parse(savedData);
                    Object.entries(parsed).forEach(([key, value]) => {
                        // false = jangan trigger request server saat restore awal,
                        // biar gak langsung nembak banyak request pas load.
                        component.$wire.set(`data.${key}`, value, false);
                    });
                } catch (e) {
                    console.error('Gagal restore draft project:', e);
                }
            });
            Livewire.hook('commit', ({ component, respond }) => {
                respond(() => {
                    const state = component.canonical ?? null;
                    if (!state || !('data' in state)) {
                        return;
                    }

                    try {
                        localStorage.setItem(storageKey, JSON.stringify(state.data));
                    } catch (e) {
                        console.error('Gagal simpan draft project:', e);
                    }
                });
            });

            Livewire.on('project-created', () => {
                localStorage.removeItem(storageKey);
            });
        });
    </script>
</div>