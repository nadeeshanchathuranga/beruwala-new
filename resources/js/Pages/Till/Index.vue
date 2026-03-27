<template>
  <Head title="Till Management" />

  <AppLayout>
    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-4">
            <Link
              :href="route('shifts.index')"
              class="px-6 py-2.5 rounded-[5px] font-medium text-sm bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 transition-all duration-200"
            >
              ← Back
            </Link>
            <h1 class="text-3xl font-bold text-gray-900">Till Management</h1>
          </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
          <div class="text-sm text-blue-700">Active Shift</div>
          <div class="text-lg font-semibold text-blue-900">
            #{{ activeShift.id }} started at {{ formatDateTime(activeShift.start_time) }}
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
          <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-xs text-gray-500">Opening</div>
            <div class="text-xl font-semibold text-gray-900">{{ formatMoney(totals.opening_amount) }}</div>
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-xs text-gray-500">Sales</div>
            <div class="text-xl font-semibold text-gray-900">{{ formatMoney(totals.sales_total) }}</div>
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-xs text-gray-500">Cash In</div>
            <div class="text-xl font-semibold text-green-600">{{ formatMoney(totals.cash_in_total) }}</div>
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-xs text-gray-500">Cash Out</div>
            <div class="text-xl font-semibold text-red-600">{{ formatMoney(totals.cash_out_total) }}</div>
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="text-xs text-gray-500">Till Balance</div>
            <div class="text-xl font-semibold text-gray-900">{{ formatMoney(totals.balance) }}</div>
          </div>
          <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
            <div class="text-xs text-emerald-700">Available Cash</div>
            <div class="text-xl font-semibold text-emerald-700">{{ formatMoney(totals.available_cash) }}</div>
          </div>
        </div>

        <div class="text-xs text-gray-500 mb-6">
          Rule: {{ availableCashRule }}
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Record Till Transaction</h2>

          <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
              <select v-model="form.transaction_type" class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm">
                <option value="cash_in">Cash In</option>
                <option value="cash_out">Cash Out</option>
              </select>
              <p v-if="form.errors.transaction_type" class="mt-1 text-xs text-red-600">{{ form.errors.transaction_type }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
              <input
                v-model="form.amount"
                type="number"
                step="0.01"
                min="0.01"
                class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm"
              />
              <p v-if="form.errors.amount" class="mt-1 text-xs text-red-600">{{ form.errors.amount }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
              <input
                v-model="form.note"
                type="text"
                placeholder="Optional note"
                class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm"
              />
              <p v-if="form.errors.note" class="mt-1 text-xs text-red-600">{{ form.errors.note }}</p>
            </div>

            <div class="md:col-span-4 flex justify-end">
              <button
                type="submit"
                :disabled="form.processing"
                class="px-6 py-2.5 rounded-[5px] bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 disabled:opacity-60"
              >
                {{ form.processing ? "Saving..." : "Save Transaction" }}
              </button>
            </div>
          </form>
        </div>

        <div class="overflow-hidden bg-white rounded-2xl border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Transaction History</h2>
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
                  v-for="transaction in transactions.data"
                  :key="transaction.id"
                  class="border-b border-gray-100 hover:bg-gray-50"
                >
                  <td class="px-4 py-3 text-sm text-gray-700">{{ formatDateTime(transaction.created_at) }}</td>
                  <td class="px-4 py-3 text-sm text-gray-700">{{ transaction.user?.name || "N/A" }}</td>
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

                <tr v-if="!transactions.data || transactions.data.length === 0">
                  <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                    No transactions yet.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div
            class="flex items-center justify-between px-6 py-4 border-t border-gray-200"
            v-if="transactions.links && transactions.links.length > 3"
          >
            <div class="text-sm text-gray-600">
              Showing {{ transactions.from }} to {{ transactions.to }} of {{ transactions.total }} results
            </div>
            <div class="flex space-x-2">
              <button
                v-for="link in transactions.links"
                :key="link.label"
                @click="link.url ? router.visit(link.url) : null"
                :disabled="!link.url"
                :class="[
                  'px-3 py-1.5 rounded-[5px] text-sm font-medium transition',
                  link.active
                    ? 'bg-blue-600 text-white'
                    : link.url
                    ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                    : 'bg-gray-200 text-gray-400 cursor-not-allowed',
                ]"
                v-html="link.label"
              ></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";

defineProps({
  activeShift: {
    type: Object,
    required: true,
  },
  transactions: {
    type: Object,
    required: true,
  },
  totals: {
    type: Object,
    required: true,
  },
  availableCashRule: {
    type: String,
    default: "",
  },
});

const form = useForm({
  transaction_type: "cash_in",
  amount: "",
  note: "",
});

const submit = () => {
  form.post(route("till.store"), {
    preserveScroll: true,
    onSuccess: () => {
      form.amount = "";
      form.note = "";
      form.clearErrors();
    },
  });
};

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
</script>
