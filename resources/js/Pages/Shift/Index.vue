<template>
  <Head title="Shifts" />

  <AppLayout>
    <div class="min-h-screen bg-gray-50 p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-6 py-2.5 rounded-[5px] font-medium text-sm bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 transition-all duration-200"
          >
            ← Back
          </button>
          <h1 class="text-3xl font-bold text-gray-900">Shift Management</h1>
        </div>

        <div class="flex items-center gap-2">
          <Link
            :href="route('till.index')"
            class="px-6 py-2.5 rounded-[5px] font-medium text-sm bg-emerald-600 hover:bg-emerald-700 text-white transition-all duration-200"
          >
            Till Management
          </Link>
          <Link
            :href="route('shifts.create')"
            class="px-6 py-2.5 rounded-[5px] font-medium text-sm bg-blue-600 hover:bg-blue-700 text-white transition-all duration-200"
          >
            Start Shift
          </Link>
        </div>
      </div>

      <div
        v-if="activeShift"
        class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3"
      >
        <div>
          <div class="text-sm text-blue-700">Current Active Shift</div>
          <div class="text-lg font-semibold text-blue-900">
            Started {{ formatDateTime(activeShift.start_time) }} with {{ formatMoney(activeShift.start_amount) }}
          </div>
        </div>
        <div class="flex gap-2">
          <Link
            :href="route('shifts.show', activeShift.id)"
            class="px-4 py-2 text-sm font-medium rounded-[5px] bg-blue-600 text-white hover:bg-blue-700"
          >
            View
          </Link>
          <Link
            :href="route('shifts.edit', activeShift.id)"
            class="px-4 py-2 text-sm font-medium rounded-[5px] bg-red-600 text-white hover:bg-red-700"
          >
            End Shift
          </Link>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <input
            v-model="filterForm.search"
            type="text"
            placeholder="Search by user or note"
            class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
          />

          <select
            v-model="filterForm.status"
            class="w-full border border-gray-300 rounded-[5px] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
          >
            <option value="">All Statuses</option>
            <option value="open">Open</option>
            <option value="closed">Closed</option>
          </select>

          <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input v-model="filterForm.mine" type="checkbox" class="rounded border-gray-300" />
            Show only my shifts
          </label>

          <div class="flex gap-2">
            <button
              @click="applyFilters"
              class="px-4 py-2 bg-blue-600 text-white rounded-[5px] text-sm font-medium hover:bg-blue-700 transition"
            >
              Filter
            </button>
            <button
              @click="resetFilters"
              class="px-4 py-2 bg-gray-100 text-gray-700 rounded-[5px] text-sm font-medium hover:bg-gray-200 transition"
            >
              Reset
            </button>
          </div>
        </div>
      </div>

      <div class="overflow-hidden bg-white rounded-2xl border border-gray-200">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b-2 border-blue-600">
                <th class="px-4 py-3 text-blue-600 font-semibold text-sm">#</th>
                <th class="px-4 py-3 text-blue-600 font-semibold text-sm">User</th>
                <th class="px-4 py-3 text-blue-600 font-semibold text-sm">Start</th>
                <th class="px-4 py-3 text-blue-600 font-semibold text-sm">End</th>
                <th class="px-4 py-3 text-blue-600 font-semibold text-sm text-right">Start Amt</th>
                <th class="px-4 py-3 text-blue-600 font-semibold text-sm text-right">Expected</th>
                <th class="px-4 py-3 text-blue-600 font-semibold text-sm text-center">Status</th>
                <th class="px-4 py-3 text-blue-600 font-semibold text-sm text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(shift, index) in shifts.data"
                :key="shift.id"
                class="border-b border-gray-200 hover:bg-gray-50 transition"
              >
                <td class="px-4 py-3 text-sm text-gray-700">
                  {{ (shifts.current_page - 1) * shifts.per_page + index + 1 }}
                </td>
                <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ shift.user?.name || 'N/A' }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ formatDateTime(shift.start_time) }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ shift.end_time ? formatDateTime(shift.end_time) : '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 text-right">{{ formatMoney(shift.start_amount) }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 text-right">{{ formatMoney(shift.expected_cash || 0) }}</td>
                <td class="px-4 py-3 text-center">
                  <span
                    :class="shift.status === 'open'
                      ? 'bg-green-500 text-white px-3 py-1 rounded-[5px] text-xs font-semibold'
                      : 'bg-gray-500 text-white px-3 py-1 rounded-[5px] text-xs font-semibold'"
                  >
                    {{ shift.status }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex justify-center gap-2">
                    <Link
                      :href="route('shifts.show', shift.id)"
                      class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-[5px] hover:bg-green-700"
                    >
                      Show
                    </Link>
                    <Link
                      v-if="shift.status === 'open'"
                      :href="route('shifts.edit', shift.id)"
                      class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-[5px] hover:bg-red-700"
                    >
                      End
                    </Link>
                    <button
                      v-if="shift.status === 'closed'"
                      @click="deleteShift(shift.id)"
                      class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-[5px] hover:bg-red-700"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!shifts.data || shifts.data.length === 0">
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">No shifts found</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          class="flex items-center justify-between px-6 py-4 border-t border-gray-200"
          v-if="shifts.links && shifts.links.length > 3"
        >
          <div class="text-sm text-gray-600">
            Showing {{ shifts.from }} to {{ shifts.to }} of {{ shifts.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in shifts.links"
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
  </AppLayout>
</template>

<script setup>
import { reactive } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";

const props = defineProps({
  shifts: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  activeShift: {
    type: Object,
    default: null,
  },
});

const filterForm = reactive({
  search: props.filters?.search || "",
  status: props.filters?.status || "",
  mine: !!props.filters?.mine,
});

const applyFilters = () => {
  router.get(route("shifts.index"), filterForm, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  filterForm.search = "";
  filterForm.status = "";
  filterForm.mine = false;
  applyFilters();
};

const deleteShift = (id) => {
  if (!confirm("Are you sure you want to delete this shift?")) {
    return;
  }

  router.delete(route("shifts.destroy", id));
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
