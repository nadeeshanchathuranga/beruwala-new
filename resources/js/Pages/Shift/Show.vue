<template>
  <Head :title="`Shift #${shift.id}`" />

  <AppLayout>
    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-4">
            <Link
              :href="route('shifts.index')"
              class="px-6 py-2.5 rounded-[5px] font-medium text-sm bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 transition-all duration-200"
            >
              ← Back
            </Link>
            <h1 class="text-3xl font-bold text-gray-900">Shift #{{ shift.id }}</h1>
          </div>

          <div class="flex gap-2">
            <Link
              :href="route('till.index')"
              class="px-6 py-2.5 rounded-[5px] font-medium text-sm bg-emerald-600 hover:bg-emerald-700 text-white transition-all duration-200"
            >
              Till
            </Link>
            <Link
              v-if="isCurrentUsersOpenShift"
              :href="route('shifts.edit', shift.id)"
              class="px-6 py-2.5 rounded-[5px] font-medium text-sm bg-red-600 hover:bg-red-700 text-white transition-all duration-200"
            >
              End Shift
            </Link>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
          <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-xs text-gray-500">Cash In</div>
            <div class="text-2xl font-bold text-green-600">{{ formatMoney(summary.cash_in) }}</div>
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-xs text-gray-500">Cash Out</div>
            <div class="text-2xl font-bold text-red-600">{{ formatMoney(summary.cash_out) }}</div>
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-xs text-gray-500">Net</div>
            <div class="text-2xl font-bold text-blue-600">{{ formatMoney(summary.net) }}</div>
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-xs text-gray-500">Expected Cash</div>
            <div class="text-2xl font-bold text-indigo-600">{{ formatMoney(summary.expected_cash) }}</div>
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-xs text-gray-500">Transactions</div>
            <div class="text-2xl font-bold text-gray-800">{{ shift.transactions?.length || 0 }}</div>
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Shift Details</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-gray-500">User</p>
              <p class="font-medium text-gray-900">{{ shift.user?.name || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-gray-500">Status</p>
              <p>
                <span
                  :class="shift.status === 'open'
                    ? 'bg-green-500 text-white px-3 py-1 rounded-[5px] text-xs font-semibold'
                    : 'bg-gray-600 text-white px-3 py-1 rounded-[5px] text-xs font-semibold'"
                >
                  {{ shift.status }}
                </span>
              </p>
            </div>
            <div>
              <p class="text-gray-500">Start Time</p>
              <p class="font-medium text-gray-900">{{ formatDateTime(shift.start_time) }}</p>
            </div>
            <div>
              <p class="text-gray-500">End Time</p>
              <p class="font-medium text-gray-900">{{ shift.end_time ? formatDateTime(shift.end_time) : '-' }}</p>
            </div>
            <div>
              <p class="text-gray-500">Start Amount</p>
              <p class="font-medium text-gray-900">{{ formatMoney(shift.start_amount) }}</p>
            </div>
            <div>
              <p class="text-gray-500">End Amount</p>
              <p class="font-medium text-gray-900">{{ shift.end_amount ? formatMoney(shift.end_amount) : '-' }}</p>
            </div>
            <div>
              <p class="text-gray-500">Total Sales</p>
              <p class="font-medium text-gray-900">{{ formatMoney(shift.total_sales) }}</p>
            </div>
            <div>
              <p class="text-gray-500">Expected Cash</p>
              <p class="font-medium text-gray-900">{{ formatMoney(shift.expected_cash || summary.expected_cash) }}</p>
            </div>
            <div>
              <p class="text-gray-500">Variance</p>
              <p class="font-medium" :class="varianceTextClass">{{ varianceLabel }}</p>
            </div>
          </div>

          <div class="mt-4" v-if="shift.notes">
            <p class="text-gray-500 text-sm">Notes</p>
            <p class="text-gray-800 mt-1">{{ shift.notes }}</p>
          </div>

          <div class="mt-4" v-if="shift.closing_notes">
            <p class="text-gray-500 text-sm">Closing Notes</p>
            <p class="text-gray-800 mt-1">{{ shift.closing_notes }}</p>
          </div>
        </div>

        <div class="overflow-hidden bg-white rounded-2xl border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Till Transactions</h2>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm">Date</th>
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm">User</th>
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm">Type</th>
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm">Note</th>
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm text-right">Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="transaction in shift.transactions"
                  :key="transaction.id"
                  class="border-b border-gray-100 hover:bg-gray-50"
                >
                  <td class="px-4 py-3 text-sm text-gray-700">{{ formatDateTime(transaction.created_at) }}</td>
                  <td class="px-4 py-3 text-sm text-gray-700">{{ transaction.user?.name || 'N/A' }}</td>
                  <td class="px-4 py-3">
                    <span
                      :class="transaction.transaction_type === 'cash_in'
                        ? 'bg-green-500 text-white px-2 py-1 rounded-[5px] text-xs font-medium'
                        : 'bg-red-500 text-white px-2 py-1 rounded-[5px] text-xs font-medium'"
                    >
                      {{ transaction.transaction_type }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">{{ transaction.note || '-' }}</td>
                  <td class="px-4 py-3 text-sm text-gray-700 text-right">{{ formatMoney(transaction.amount) }}</td>
                </tr>

                <tr v-if="!shift.transactions || shift.transactions.length === 0">
                  <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                    No till transactions for this shift yet.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";

const props = defineProps({
  shift: {
    type: Object,
    required: true,
  },
  summary: {
    type: Object,
    required: true,
  },
  isCurrentUsersOpenShift: {
    type: Boolean,
    default: false,
  },
});

const page = usePage();

const varianceValue = computed(() => {
  if (props.shift.variance_amount === null || props.shift.variance_amount === undefined) {
    return null;
  }

  return Number(props.shift.variance_amount);
});

const varianceLabel = computed(() => {
  if (varianceValue.value === null) {
    return "-";
  }

  if (varianceValue.value > 0) {
    return `Excess ${formatMoney(varianceValue.value)}`;
  }

  if (varianceValue.value < 0) {
    return `Short ${formatMoney(Math.abs(varianceValue.value))}`;
  }

  return "Balanced 0.00";
});

const varianceTextClass = computed(() => {
  if (varianceValue.value === null) return "text-gray-900";
  if (varianceValue.value > 0) return "text-green-700";
  if (varianceValue.value < 0) return "text-red-700";
  return "text-gray-900";
});

const formatDateTime = (value) => {
  if (!value) return "-";

  return new Date(value).toLocaleString("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const formatMoney = (value) => {
  return Number(value || 0).toLocaleString("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

onMounted(() => {
  const shiftEvent = page.props?.flash?.shift_event;

  if (!shiftEvent || !shiftEvent.type) {
    return;
  }

  window.dispatchEvent(
    new CustomEvent(`shift:${shiftEvent.type}`, {
      detail: shiftEvent,
    })
  );
});
</script>
