<template>
  <Head title="Create Shift" />

  <AppLayout>
    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
          <Link
            :href="route('shifts.index')"
            class="px-6 py-2.5 rounded-[5px] font-medium text-sm bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 transition-all duration-200"
          >
            ← Back
          </Link>
          <h1 class="text-3xl font-bold text-gray-900">Start New Shift</h1>
        </div>

        <form @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-200 p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
              <select
                v-model="form.user_id"
                class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm"
              >
                <option value="">Select user</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
              <p v-if="form.errors.user_id" class="mt-1 text-xs text-red-600">{{ form.errors.user_id }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                v-model="form.status"
                class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm"
              >
                <option value="open">Open</option>
                <option value="closed">Closed</option>
              </select>
              <p v-if="form.errors.status" class="mt-1 text-xs text-red-600">{{ form.errors.status }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
              <input
                v-model="form.start_time"
                type="datetime-local"
                class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm"
              />
              <p v-if="form.errors.start_time" class="mt-1 text-xs text-red-600">{{ form.errors.start_time }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
              <input
                v-model="form.end_time"
                type="datetime-local"
                class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm"
              />
              <p v-if="form.errors.end_time" class="mt-1 text-xs text-red-600">{{ form.errors.end_time }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Start Amount</label>
              <input
                v-model="form.start_amount"
                type="number"
                min="0"
                step="0.01"
                class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm"
              />
              <p v-if="form.errors.start_amount" class="mt-1 text-xs text-red-600">{{ form.errors.start_amount }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">End Amount</label>
              <input
                v-model="form.end_amount"
                type="number"
                min="0"
                step="0.01"
                class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm"
              />
              <p v-if="form.errors.end_amount" class="mt-1 text-xs text-red-600">{{ form.errors.end_amount }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Total Sales</label>
              <input
                v-model="form.total_sales"
                type="number"
                min="0"
                step="0.01"
                class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm"
              />
              <p v-if="form.errors.total_sales" class="mt-1 text-xs text-red-600">{{ form.errors.total_sales }}</p>
            </div>
          </div>

          <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm"
            ></textarea>
            <p v-if="form.errors.notes" class="mt-1 text-xs text-red-600">{{ form.errors.notes }}</p>
          </div>

          <div class="mt-6 flex gap-3">
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2.5 rounded-[5px] bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 disabled:opacity-60"
            >
              {{ form.processing ? "Saving..." : "Create Shift" }}
            </button>
            <Link
              :href="route('shifts.index')"
              class="px-6 py-2.5 rounded-[5px] bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200"
            >
              Cancel
            </Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";

const props = defineProps({
  users: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();

const now = new Date();
const localNow = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}T${String(now.getHours()).padStart(2, "0")}:${String(now.getMinutes()).padStart(2, "0")}`;

const form = useForm({
  user_id: page.props?.auth?.user?.id || "",
  start_time: localNow,
  start_amount: "0.00",
  end_time: "",
  end_amount: "",
  total_sales: "0.00",
  notes: "",
  status: "open",
});

const submit = () => {
  form.post(route("shifts.store"));
};
</script>
