<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Xabar shablonlari</h1>
        <p class="mt-1 text-surface-600 dark:text-surface-400">Nomzodlarga tez xabar yuborish uchun shablonlar</p>
      </div>
      <AppButton variant="primary" @click="showCreateModal = true">
        <template #icon-left>
          <PlusIcon class="w-5 h-5" />
        </template>
        Yangi shablon
      </AppButton>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="w-8 h-8 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Empty state -->
    <AppCard v-else-if="templates.length === 0">
      <div class="text-center py-12">
        <ChatBubbleLeftRightIcon class="w-16 h-16 mx-auto text-surface-300 dark:text-surface-600" />
        <h3 class="mt-4 text-lg font-medium text-surface-900 dark:text-surface-100">
          Shablonlar yo'q
        </h3>
        <p class="mt-2 text-sm text-surface-500 dark:text-surface-400 max-w-md mx-auto">
          Nomzodlarga xabar yuborish uchun shablonlar yarating.
          O'zgaruvchilardan foydalanib, shaxsiylashtirilgan xabarlar yuboring.
        </p>
        <AppButton variant="primary" class="mt-4" @click="showCreateModal = true">
          Birinchi shablonni yaratish
        </AppButton>
      </div>
    </AppCard>

    <!-- Templates list -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <AppCard v-for="template in templates" :key="template.id">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="font-medium text-surface-900 dark:text-surface-100">{{ template.name }}</h3>
            <div class="flex items-center gap-2 mt-1">
              <span
                class="px-2 py-0.5 text-xs rounded-full"
                :class="template.is_system
                  ? 'bg-info-100 dark:bg-info-900 text-info-700 dark:text-info-300'
                  : 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400'"
              >
                {{ template.is_system ? 'Tizim' : template.type || 'Shaxsiy' }}
              </span>
            </div>
          </div>
        </div>

        <div class="p-3 bg-surface-50 dark:bg-surface-800 rounded-lg">
          <p class="text-sm text-surface-700 dark:text-surface-300 whitespace-pre-line line-clamp-4">
            {{ template.body_uz }}
          </p>
        </div>

        <div v-if="template.variables?.length" class="mt-3">
          <p class="text-xs text-surface-500 dark:text-surface-400 mb-1">O'zgaruvchilar:</p>
          <div class="flex flex-wrap gap-1">
            <code
              v-for="v in template.variables"
              :key="v"
              class="px-1.5 py-0.5 text-xs bg-warning-100 dark:bg-warning-900 text-warning-700 dark:text-warning-300 rounded"
            >
              {{ '{' + v + '}' }}
            </code>
          </div>
        </div>
      </AppCard>
    </div>

    <!-- Create Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
      @click.self="showCreateModal = false"
    >
      <div class="w-full max-w-lg bg-surface-0 dark:bg-surface-900 rounded-2xl shadow-xl">
        <div class="p-6 border-b border-surface-200 dark:border-surface-800">
          <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Yangi shablon yaratish</h2>
        </div>

        <form @submit.prevent="createTemplate" class="p-6 space-y-4">
          <AppInput
            v-model="form.name"
            label="Shablon nomi"
            placeholder="Masalan: Suhbatga taklif"
            :error="formErrors.name"
            required
          />

          <AppInput
            v-model="form.type"
            label="Turi"
            placeholder="Masalan: invite, rejection, custom"
          />

          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">
              Xabar matni (O'zbekcha) *
            </label>
            <textarea
              v-model="form.body_uz"
              rows="5"
              class="w-full px-3 py-2 border border-surface-300 dark:border-surface-700 rounded-lg bg-surface-0 dark:bg-surface-800 text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"
              placeholder="Hurmatli {worker_name}, sizni {vacancy_title} vakansiyasi bo'yicha suhbatga taklif qilamiz..."
              required
            ></textarea>
            <p v-if="formErrors.body_uz" class="mt-1 text-sm text-danger-500">{{ formErrors.body_uz }}</p>
            <p class="mt-1 text-xs text-surface-400">
              O'zgaruvchilar: {worker_name}, {vacancy_title}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">
              Xabar matni (Ruscha)
            </label>
            <textarea
              v-model="form.body_ru"
              rows="3"
              class="w-full px-3 py-2 border border-surface-300 dark:border-surface-700 rounded-lg bg-surface-0 dark:bg-surface-800 text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"
              placeholder="Ixtiyoriy: Ruscha versiya"
            ></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <AppButton variant="outline" type="button" @click="showCreateModal = false">
              Bekor qilish
            </AppButton>
            <AppButton variant="primary" type="submit" :loading="saving">
              Saqlash
            </AppButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AppCard from '../../components/ui/AppCard.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppButton from '../../components/ui/AppButton.vue';
import { PlusIcon, ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline';

const loading = ref(true);
const templates = ref([]);
const showCreateModal = ref(false);
const saving = ref(false);
const formErrors = ref({});

const form = ref({
  name: '',
  type: '',
  body_uz: '',
  body_ru: '',
});

async function fetchTemplates() {
  loading.value = true;
  try {
    const response = await axios.get('/api/recruiter/message-templates');
    templates.value = response.data.templates || [];
  } catch (e) {
    console.error('Message templates error:', e);
  } finally {
    loading.value = false;
  }
}

async function createTemplate() {
  saving.value = true;
  formErrors.value = {};

  try {
    const response = await axios.post('/api/recruiter/message-templates', {
      name: form.value.name,
      type: form.value.type || 'custom',
      body_uz: form.value.body_uz,
      body_ru: form.value.body_ru || null,
    });

    templates.value.push(response.data.template);
    showCreateModal.value = false;
    form.value = { name: '', type: '', body_uz: '', body_ru: '' };
    toast.success('Shablon yaratildi');
  } catch (e) {
    if (e.response?.status === 422) {
      formErrors.value = e.response.data.errors || {};
    }
    toast.error(e.response?.data?.message || 'Xatolik yuz berdi');
  } finally {
    saving.value = false;
  }
}

onMounted(() => fetchTemplates());
</script>
