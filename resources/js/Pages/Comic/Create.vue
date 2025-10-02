<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    title: '',
    author: '',
    pdf: null,
});

function submit() {
    form.post(route('comics.store'), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Tambah Komik" />
    <AuthenticatedLayout>
        <div class="max-w-xl mx-auto py-10">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-800">Tambah Komik</h1>
                <Link :href="route('comics.index')" class="text-blue-600 hover:underline">← Kembali</Link>
            </div>
            <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-5">
                <div>
                    <label class="block font-semibold mb-1">Judul</label>
                    <input v-model="form.title" type="text" class="w-full border rounded px-3 py-2" required autofocus />
                    <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Penulis</label>
                    <input v-model="form.author" type="text" class="w-full border rounded px-3 py-2" />
                    <div v-if="form.errors.author" class="text-red-500 text-sm mt-1">{{ form.errors.author }}</div>
                </div>
                <div>
                    <label class="block font-semibold mb-1">File PDF</label>
                    <input
                        type="file"
                        accept="application/pdf"
                        @change="e => form.pdf = e.target.files[0]"
                        class="w-full border rounded px-3 py-2"
                        required
                    />
                    <div v-if="form.errors.pdf" class="text-red-500 text-sm mt-1">{{ form.errors.pdf }}</div>
                </div>
                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded shadow hover:bg-blue-700 transition"
                        :disabled="form.processing"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>