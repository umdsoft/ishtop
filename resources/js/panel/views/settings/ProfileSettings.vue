<template>
  <div class="space-y-6">
    <!-- Profile Photo -->
    <AppCard>
      <template #header>
        <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Profil rasmi</h2>
      </template>

      <div class="flex items-start gap-6">
        <AppAvatar
          :name="`${form.first_name} ${form.last_name}`"
          :src="form.avatar"
          size="2xl"
          color="brand"
        />
        <div class="flex-1">
          <p class="text-sm text-surface-600 dark:text-surface-400 mb-3">
            JPG, PNG yoki GIF. Maksimal 2MB.
          </p>
          <div class="flex gap-3">
            <AppButton variant="outline" size="sm">
              <template #icon-left>
                <ArrowUpTrayIcon class="h-4 w-4" />
              </template>
              Rasm yuklash
            </AppButton>
            <AppButton v-if="form.avatar" variant="ghost" size="sm">
              O'chirish
            </AppButton>
          </div>
        </div>
      </div>
    </AppCard>

    <!-- Personal Information -->
    <AppCard>
      <template #header>
        <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Shaxsiy ma'lumotlar</h2>
      </template>

      <form @submit.prevent="saveProfile" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <AppInput
            v-model="form.first_name"
            label="Ism"
            placeholder="Umidbek"
            required
            :error="errors.first_name"
          />

          <AppInput
            v-model="form.last_name"
            label="Familiya"
            placeholder="Karimov"
            required
            :error="errors.last_name"
          />
        </div>

        <AppInput
          v-model="form.email"
          type="email"
          label="Email"
          placeholder="umidbek@company.uz"
          required
          :error="errors.email"
        />

        <AppInput
          v-model="form.phone"
          type="tel"
          label="Telefon raqami"
          placeholder="+998 90 123 45 67"
          :error="errors.phone"
        />

        <AppInput
          v-model="form.position"
          label="Lavozim"
          placeholder="HR Manager"
          :error="errors.position"
        />

        <div class="flex justify-end gap-3 pt-4">
          <AppButton type="button" variant="outline" @click="resetForm">
            Bekor qilish
          </AppButton>
          <AppButton type="submit" variant="primary" :loading="saving">
            O'zgarishlarni saqlash
          </AppButton>
        </div>
      </form>
    </AppCard>

    <!-- Change Password -->
    <AppCard>
      <template #header>
        <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Parolni o'zgartirish</h2>
      </template>

      <form @submit.prevent="changePassword" class="space-y-4">
        <AppInput
          v-model="passwordForm.current_password"
          type="password"
          label="Joriy parol"
          placeholder="••••••••"
          required
          :error="passwordErrors.current_password"
        />

        <AppInput
          v-model="passwordForm.new_password"
          type="password"
          label="Yangi parol"
          placeholder="••••••••"
          required
          hint="Kamida 8 ta belgi"
          :error="passwordErrors.new_password"
        />

        <AppInput
          v-model="passwordForm.new_password_confirmation"
          type="password"
          label="Yangi parolni tasdiqlang"
          placeholder="••••••••"
          required
          :error="passwordErrors.new_password_confirmation"
        />

        <div class="flex justify-end pt-4">
          <AppButton type="submit" variant="primary" :loading="changingPassword">
            Parolni o'zgartirish
          </AppButton>
        </div>
      </form>
    </AppCard>

    <!-- Telegram Integration -->
    <AppCard>
      <template #header>
        <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Telegram integratsiyasi</h2>
      </template>

      <div v-if="form.telegram_connected">
        <div class="flex items-center justify-between p-4 bg-success-50 dark:bg-success-900/20 rounded-lg mb-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-success-100 dark:bg-success-900/50 flex items-center justify-center">
              <CheckCircleIcon class="h-6 w-6 text-success-600 dark:text-success-400" />
            </div>
            <div>
              <p class="font-medium text-success-900 dark:text-success-100">Telegram ulangan</p>
              <p class="text-sm text-success-700 dark:text-success-300">@{{ form.telegram_username }}</p>
            </div>
          </div>
          <AppButton variant="outline" size="sm">
            Uzish
          </AppButton>
        </div>
      </div>
      <div v-else>
        <div class="flex items-center justify-between p-4 bg-surface-50 dark:bg-surface-900 rounded-lg mb-4">
          <div>
            <p class="font-medium text-surface-900 dark:text-surface-100 mb-1">Telegramni ulang</p>
            <p class="text-sm text-surface-600 dark:text-surface-400">
              Botimiz orqali tez bildirishnomalar oling
            </p>
          </div>
          <AppButton variant="primary" size="sm">
            <template #icon-left>
              <span class="text-lg">📱</span>
            </template>
            Ulash
          </AppButton>
        </div>
      </div>
    </AppCard>

    <!-- Danger Zone -->
    <AppCard>
      <template #header>
        <h2 class="text-lg font-semibold text-danger-600 dark:text-danger-400">Xavfli zona</h2>
      </template>

      <div class="space-y-4">
        <div class="flex items-start justify-between">
          <div>
            <p class="font-medium text-surface-900 dark:text-surface-100 mb-1">Akkauntni o'chirish</p>
            <p class="text-sm text-surface-600 dark:text-surface-400">
              Akkauntingiz va barcha ma'lumotlaringiz butunlay o'chiriladi. Bu amalni qaytarib bo'lmaydi.
            </p>
          </div>
          <AppButton variant="danger" size="sm" @click="showDeleteDialog = true">
            Akkauntni o'chirish
          </AppButton>
        </div>
      </div>
    </AppCard>

    <!-- Delete Confirmation -->
    <AppConfirmDialog
      :open="showDeleteDialog"
      type="danger"
      title="Akkauntni o'chirish"
      message="Akkauntingiz va barcha ma'lumotlaringiz butunlay o'chiriladi. Bu amalni qaytarib bo'lmaydi. Davom etishni xohlaysizmi?"
      confirm-text="Ha, o'chirish"
      cancel-text="Bekor qilish"
      @confirm="deleteAccount"
      @cancel="showDeleteDialog = false"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { ArrowUpTrayIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';
import AppCard from '../../components/ui/AppCard.vue';
import AppButton from '../../components/ui/AppButton.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppAvatar from '../../components/ui/AppAvatar.vue';
import AppConfirmDialog from '../../components/ui/AppConfirmDialog.vue';

const form = ref({
  first_name: 'Umidbek',
  last_name: 'Karimov',
  email: 'umidbek@company.uz',
  phone: '+998901234567',
  position: 'HR Manager',
  avatar: null,
  telegram_connected: true,
  telegram_username: 'umidbek_karimov',
});

const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
});

const errors = ref({});
const passwordErrors = ref({});
const saving = ref(false);
const changingPassword = ref(false);
const showDeleteDialog = ref(false);

const originalForm = ref({...form.value});

async function saveProfile() {
  errors.value = {};
  saving.value = true;

  try {
    await new Promise(resolve => setTimeout(resolve, 1000));
    originalForm.value = {...form.value};
    toast.success('Profil saqlandi');
  } catch (error) {
    toast.error('Xatolik yuz berdi');
  } finally {
    saving.value = false;
  }
}

function resetForm() {
  form.value = {...originalForm.value};
}

async function changePassword() {
  passwordErrors.value = {};

  if (passwordForm.value.new_password !== passwordForm.value.new_password_confirmation) {
    passwordErrors.value.new_password_confirmation = 'Parollar mos emas';
    return;
  }

  if (passwordForm.value.new_password.length < 8) {
    passwordErrors.value.new_password = 'Parol kamida 8 ta belgidan iborat bo\'lishi kerak';
    return;
  }

  changingPassword.value = true;

  try {
    await new Promise(resolve => setTimeout(resolve, 1000));
    passwordForm.value = {
      current_password: '',
      new_password: '',
      new_password_confirmation: '',
    };
    toast.success('Parol o\'zgartirildi');
  } catch (error) {
    toast.error('Xatolik yuz berdi');
  } finally {
    changingPassword.value = false;
  }
}

async function deleteAccount() {
  await new Promise(resolve => setTimeout(resolve, 1000));
  showDeleteDialog.value = false;
  toast.success('Akkaunt o\'chirildi');
  // Redirect to login
}
</script>
