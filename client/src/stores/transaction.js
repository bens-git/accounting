import { defineStore } from "pinia";
import apiClient from "@/axios";
import { useLoadingStore } from "./loading";
import { useResponseStore } from "./response";
import { useDraftStore } from "./draft";

export const useTransactionStore = defineStore("transaction", {
  state: () => ({
    transactions: [],
    totalTransactions: 0,
    search: "",
    selectedTransactionId: null,
    selectedTransaction: null,
    selectedType: "PURCHASE",
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
      this.fetchTransactions();
    },

    async fetchTransactions() {
      const loadingStore = useLoadingStore();
      const responseStore = useResponseStore();
      loadingStore.startLoading("fetchTransactions");

      try {
        const { data } = await apiClient.get("/transactions", {
          params: {
            page: this.page,
            itemsPerPage: this.itemsPerPage,
            sortBy: this.sortBy,
            order: this.order,
            search: this.search,
            type: this.selectedType,
            tag: this.selectedTag,
            month: this.selectedMonth,
            year: this.selectedYear,
          },
        });
        this.transactions = data.transactions;
        this.totalTransactions = data.count;
      } catch (error) {
        console.log(error);
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("fetchTransactions");
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

      this.fetchTransactions();
    },

    async createTransaction(data) {
      const responseStore = useResponseStore();
      const loadingStore = useLoadingStore();
      loadingStore.startLoading("createTransaction");

      try {
        const response = await apiClient.post("/transactions", data, {
          headers: { "Content-Type": "multipart/form-data" },
        });
        console.log(response);
        this.selectedTransactionId = response.data.id;
        this.fetchTransactions();
        responseStore.setResponse(true, "Transaction created successfully");
      } catch (error) {
        console.log(error);
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("createTransaction");
      }
    },

    async createParty(data) {
      const responseStore = useResponseStore();
      const loadingStore = useLoadingStore();
      const draftStore = useDraftStore();
      loadingStore.startLoading("createParty");

      try {
        const response = await apiClient.post("/parties", data, {
          headers: { "Content-Type": "multipart/form-data" },
        });
        this.selectedPartyId = response.data.id;
        draftStore.selectedPartyId = response.data.id;
        this.fetchParties();
        draftStore.fetchParties();
        responseStore.setResponse(true, "Party created successfully");
      } catch (error) {
        console.log(error);
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("createParty");
      }
    },

    async updateTransaction(data) {
      const responseStore = useResponseStore();
      const loadingStore = useLoadingStore();
      loadingStore.startLoading("updateTransaction");

      try {
        // Create a new FormData object
        const formData = new FormData();

        // Append all item data fields to formData
        for (const [key, value] of data.entries()) {
          formData.append(key, value);
        }

        const response = await apiClient.post(
          `/update-transaction/${data.get("id")}`,
          formData,
          {
            headers: { "Content-Type": "multipart/form-data" },
          }
        );

        this.fetchTransactions();

        responseStore.setResponse(true, "Transaction updated successfully");
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("updateTransaction");
      }
    },

    async deleteTransaction() {
      const responseStore = useResponseStore();
      const loadingStore = useLoadingStore();
      loadingStore.startLoading("deleteTransaction");

      try {
        await apiClient.delete(`/transactions/${this.selectedTransaction.id}`);
        this.selectedTransaction = null;
        this.selectedTransactionId = null;
        this.fetchTransactions();
        responseStore.setResponse(true, "Transaction deleted successfully");
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("deleteTransaction");
      }
    },
  },
});
