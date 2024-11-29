import { defineStore } from "pinia";
import apiClient from "@/axios";
import { useLoadingStore } from "./loading";
import { useResponseStore } from "./response";

export const useDraftStore = defineStore("draft", {
  state: () => ({
    paginatedDrafts: [],
    totalDrafts: 0,
    search: "",
    selectedDraftId: null,
    selectedDraft: null,
    selectedType: "BILL",
    selectedMonth: null,
    selectedYear: null,
    selectedPartyId: null,
    page: 1,
    itemsPerPage: 10,
    sortBy: "date",
    order: "asc",
    types: [],
    paymentMethods: [],
    recurrenceTypes: [],
    tags: [],
    users: [],
    months: [
      "January",
      "February",
      "March",
      "April",
      "May",
      "June",
      "July",
      "August",
      "September",
      "October",
      "November",
      "December",
    ],
  }),
  actions: {
    updateOptions({ page, itemsPerPage, sortBy }) {
      this.page = page;
      this.itemsPerPage = itemsPerPage;
      this.sortBy = sortBy;

      const now = new Date();
      if (!this.selectedMonth) {
        this.selectedMonth = this.months[now.getMonth()];
      } // Current month
      if (!this.selectedYear) {
        this.selectedYear = now.getFullYear();
      } // Current year

      this.fetchTypes();
      this.fetchDrafts();
    },

    async fetchDrafts() {
      const loadingStore = useLoadingStore();
      const responseStore = useResponseStore();
      loadingStore.startLoading("fetchDrafts");

      try {
        const { data } = await apiClient.get("/drafts", {
          params: {
            page: this.page,
            itemsPerPage: this.itemsPerPage,
            sortBy: this.sortBy,
            order: this.order,
            search: this.search,
            type: this.selectedType,
            month: this.selectedMonth,
            year: this.selectedYear,
          },
        });
        this.paginatedDrafts = data.drafts;
        this.totalDrafts = data.count;
      } catch (error) {
        console.log(error);
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("fetchDrafts");
      }
    },

    async fetchTypes() {
      const loadingStore = useLoadingStore();
      const responseStore = useResponseStore();
      loadingStore.startLoading("fetchTypes");

      try {
        const { data } = await apiClient.get("/types", {});
        this.types = data;
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("fetchTypes");
      }
    },

    async fetchPaymentMethods() {
      const loadingStore = useLoadingStore();
      const responseStore = useResponseStore();
      loadingStore.startLoading("fetchPaymentMethods");

      try {
        const { data } = await apiClient.get("/payment-methods", {});
        this.paymentMethods = data;
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("fetchPaymentMethods");
      }
    },

    async fetchRecurrenceTypes() {
      const loadingStore = useLoadingStore();
      const responseStore = useResponseStore();
      loadingStore.startLoading("fetchRecurrenceTypes");

      try {
        const { data } = await apiClient.get("/recurrence-types", {});
        this.recurrenceTypes = data;
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("fetchRecurrenceTypes");
      }
    },

    async fetchTags() {
      const loadingStore = useLoadingStore();
      const responseStore = useResponseStore();
      loadingStore.startLoading("fetchTags");

      try {
        const { data } = await apiClient.get("/tags", {});
        this.tags = data;
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("fetchTags");
      }
    },

    async fetchParties() {
      const loadingStore = useLoadingStore();
      const responseStore = useResponseStore();
      loadingStore.startLoading("fetchParties");

      try {
        const { data } = await apiClient.get("/parties", {});
        this.parties = data.parties;
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("fetchParties");
      }
    },

    async fetchUsers() {
      const loadingStore = useLoadingStore();
      const responseStore = useResponseStore();
      loadingStore.startLoading("fetchUsers");

      try {
        const { data } = await apiClient.get("/users", {});
        this.users = data.users;
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("fetchUsers");
      }
    },

    resetFilters() {
      this.$reset();
      this.updateOptions(this.page, this.itemsPerPage, this.sortBy);

      this.fetchDrafts();
    },

    async createDraft(data) {
      const responseStore = useResponseStore();
      const loadingStore = useLoadingStore();
      loadingStore.startLoading("createDraft");

      try {
        const response = await apiClient.post("/drafts", data, {
          headers: { "Content-Type": "multipart/form-data" },
        });
        console.log(response);
        this.selectedDraftId = response.data.id;
        this.fetchDrafts();
        responseStore.setResponse(true, "Draft created successfully");
      } catch (error) {
        console.log(error);
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("createDraft");
      }
    },

    async updateDraft(data) {
      const responseStore = useResponseStore();
      const loadingStore = useLoadingStore();
      loadingStore.startLoading("updateDraft");

      try {
        // Create a new FormData object
        const formData = new FormData();

        // Append all item data fields to formData
        for (const [key, value] of data.entries()) {
          formData.append(key, value);
        }

        const response = await apiClient.post(
          `/update-draft/${data.get("id")}`,
          formData,
          {
            headers: { "Content-Type": "multipart/form-data" },
          }
        );

        this.fetchDrafts();

        responseStore.setResponse(true, "Draft updated successfully");
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("updateDraft");
      }
    },

    async deleteDraft() {
      const responseStore = useResponseStore();
      const loadingStore = useLoadingStore();
      loadingStore.startLoading("deleteDraft");

      try {
        await apiClient.delete(`/drafts/${this.selectedDraft.id}`);
        this.selectedDraft = null;
        this.selectedDraftId = null;
        this.fetchDrafts();
        responseStore.setResponse(true, "Draft deleted successfully");
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("deleteDraft");
      }
    },
  },
});
