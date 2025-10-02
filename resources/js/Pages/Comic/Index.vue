<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ comics: { type: Array, default: () => [] } });

function destroy(id) {
    if (confirm("Yakin ingin menghapus komik ini?")) {
        router.delete(route('comics.destroy', id));
    }
}
</script>

<template>
    <Head title="Kelola Komik" />
    <AuthenticatedLayout>
        <div class="max-w-6xl py-10 mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Kelola Komik</h1>
                <Link
                    :href="route('comics.create')"
                    class="px-4 py-2 text-white transition bg-blue-600 rounded-lg shadow hover:bg-blue-700"
                >
                    + Tambah Komik
                </Link>
            </div>

            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Judul</th>
                            <th class="px-4 py-2 text-left">Penulis</th>
                            <th class="px-4 py-2 text-left">File PDF</th>
                            <th class="px-4 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="comic in comics" :key="comic.id" class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">{{ comic.id }}</td>
                            <td class="px-4 py-2">{{ comic.title }}</td>
                            <td class="px-4 py-2">{{ comic.author }}</td>
                            <td class="px-4 py-2">
                                <a
                                    v-if="comic.pdf_path"
                                    :href="`/storage/${comic.pdf_path}`"
                                    target="_blank"
                                    rel="noopener"
                                    class="text-blue-600 hover:underline"
                                >
                                    Lihat PDF
                                </a>
                                <span v-else class="text-gray-400"> - </span>
                            </td>
                            <td class="px-4 py-2 space-x-2 text-center">
                                <Link
                                    :href="route('comics.edit', comic.id)"
                                    class="inline-block px-3 py-1 text-white transition bg-yellow-400 rounded hover:bg-yellow-500"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="destroy(comic.id)"
                                    class="inline-block px-3 py-1 text-white transition bg-red-600 rounded hover:bg-red-700"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        <tr v-if="comics.length === 0">
                            <td colspan="5" class="py-6 text-center text-gray-400">Belum ada komik.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>