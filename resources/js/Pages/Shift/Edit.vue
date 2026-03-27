<template>
  <Head title="End Shift" />

  <AppLayout>
    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
          <Link
            :href="route('shifts.show', shift.id)"
            class="px-6 py-2.5 rounded-[5px] font-medium text-sm bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 transition-all duration-200"
          >
            ← Back
          </Link>
          <h1 class="text-3xl font-bold text-gray-900">End Shift #{{ shift.id }}</h1>
        </div>

        <form @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-200 p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
              <p class="text-xs text-gray-500">Opening Cash</p>
              <p class="text-xl font-semibold text-gray-900">{{ formatMoney(shift.start_amount) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
              <p class="text-xs text-gray-500">Sales Total</p>
              <p class="text-xl font-semibold text-gray-900">{{ formatMoney(summary.sales_total) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
              <p class="text-xs text-gray-500">Cash In / Cash Out</p>
              <p class="text-xl font-semibold text-gray-900">
                {{ formatMoney(summary.cash_in) }} / {{ formatMoney(summary.cash_out) }}
              </p>
            </div>
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
              <p class="text-xs text-blue-700">Expected Closing Cash</p>
              <p class="text-xl font-semibold text-blue-900">{{ formatMoney(summary.expected_cash) }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Counted Closing Cash</label>
              <input
                v-model="form.end_amount"
                type="number"
                min="0"
                step="0.01"
                class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm"
              />
              <p v-if="form.errors.end_amount" class="mt-1 text-xs text-red-600">{{ form.errors.end_amount }}</p>
            </div>

            <div class="rounded-lg border p-4" :class="varianceClass">
              <p class="text-xs" :class="varianceTextClass">Projected Variance</p>
              <p class="text-xl font-semibold" :class="varianceTextClass">{{ varianceLabel }}</p>
            </div>
          </div>

          <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Closing Notes (Optional)</label>
            <textarea
              v-model="form.closing_notes"
              rows="3"
              class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm"
            ></textarea>
            <p v-if="form.errors.closing_notes" class="mt-1 text-xs text-red-600">{{ form.errors.closing_notes }}</p>
          </div>

          <div class="mt-6 flex gap-3">
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2.5 rounded-[5px] bg-red-600 text-white text-sm font-medium hover:bg-red-700 disabled:opacity-60"
            >
              {{ form.processing ? "Ending..." : "End Shift" }}
            </button>
            <Link
              :href="route('shifts.show', shift.id)"
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
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
  shift: {
    type: Object,
    required: true,
  },
  summary: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  end_amount: props.shift.end_amount || "",
  closing_notes: props.shift.closing_notes || "",
});

const varianceValue = computed(() => {
  const entered = Number(form.end_amount || 0);
  const expected = Number(props.summary?.expected_cash || 0);
  return entered - expected;
});

const varianceLabel = computed(() => {
  const variance = varianceValue.value;

  if (variance > 0) {
    return `Excess: ${formatMoney(variance)}`;
  }

  if (variance < 0) {
    return `Short: ${formatMoney(Math.abs(variance))}`;
  }

  return "Balanced: 0.00";
});

const varianceClass = computed(() => {
  if (varianceValue.value > 0) return "border-green-200 bg-green-50";
  if (varianceValue.value < 0) return "border-red-200 bg-red-50";
  return "border-gray-200 bg-gray-50";
});

const varianceTextClass = computed(() => {
  if (varianceValue.value > 0) return "text-green-700";
  if (varianceValue.value < 0) return "text-red-700";
  return "text-gray-700";
});

const formatMoney = (value) => {
  return Number(value || 0).toLocaleString("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const submit = () => {
  form.put(route("shifts.update", props.shift.id));
};
</script>
