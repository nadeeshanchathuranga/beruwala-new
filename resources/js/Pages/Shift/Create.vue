<template>
  <Head title="Start Shift" />

  <AppLayout>
    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-3xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
          <Link
            :href="route('shifts.index')"
            class="px-6 py-2.5 rounded-[5px] font-medium text-sm bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 transition-all duration-200"
          >
            ← Back
          </Link>
          <h1 class="text-3xl font-bold text-gray-900">Start Shift</h1>
        </div>

        <form @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-200 p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2 rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">
              Start time will be captured automatically when you click Start Shift.
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Opening Till Amount</label>
              <input
                v-model="form.start_amount"
                type="number"
                min="0"
                step="0.01"
                class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm"
              />
              <p v-if="form.errors.start_amount" class="mt-1 text-xs text-red-600">{{ form.errors.start_amount }}</p>
            </div>
          </div>

          <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Opening Notes (Optional)</label>
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
              {{ form.processing ? "Starting..." : "Start Shift" }}
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
import { Head, Link, useForm } from "@inertiajs/vue3";

const form = useForm({
  start_amount: "0.00",
  notes: "",
});

const submit = () => {
  form.post(route("shifts.store"));
};
</script>
