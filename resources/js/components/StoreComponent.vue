<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container mt-2">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <b-button variant="outline-primary" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            <b-form-group label="Name or Code:">
                                <input v-model="filterForm.store_code" type="text" class="form-control"/>
                            </b-form-group>

                            <b-button type="submit" variant="primary">Search</b-button>
                        </b-form>
                    </b-modal>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">

                        <div class="card-header">
                            <div class="card-title">
                                Stores
                            </div>
                            <div v-if="user.role == 3" class="card-tools">
                                <button
                                    class="btn btn-success"
                                    @click="newModal"
                                >
                                    Add Store <i class="fa fa-plus fa-fw"></i>
                                </button>
                            </div>
                        </div>

                        <div v-if="stores.data.length">
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <tr>
                                        <th width="200px">Name</th>
                                        <th>Code</th>
                                        <th>Address</th>
                                        <th width="250px">Actions</th>
                                    </tr>

                                    <tr v-for="store in stores.data" :key="store.id">
                                        <td>{{ store.name }}</td>
                                        <td>{{ store.code }}</td>
                                        <td>{{ store.address }}</td>
                                        <td>
                                            <a href="#" @click="onProduct(store)">
                                                Products
                                            </a>

                                            |

                                            <a href="#" @click="onSale(store)">
                                                Sales
                                            </a>

                                            |
                                            
                                            <a href="#" @click="editModal(store)">Edit</a>
                                            |
                                            <a href="#" @click="deletestore(store.id)">Delete</a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="card-footer">
                                <pagination
                                    :data="stores"
                                    @pagination-change-page="getResults"
                                ></pagination>
                            </div>
                        </div>

                        <div v-else class="p-5">
                            <div class="alert alert-info text-center">
                                No store found.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update store modal section -->
            <div
                class="modal fade"
                id="addNewstore"
                tabindex="-1"
                role="dialog"
                aria-labelledby="addNewstoreLabel"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5
                                class="modal-title"
                                id="addNewstoreLabel"
                                v-show="!editMode"
                            >
                                Add New
                            </h5>
                            <h5
                                class="modal-title"
                                id="addNewstoreLabel"
                                v-show="editMode"
                            >
                                Update Store Info
                            </h5>
                            <button
                                type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-label="Close"
                            >
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form @submit.prevent="editMode ? updatestore() : createstore()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        name="name"
                                        class="form-control"
                                        placeholder="Store Name"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'name'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="name"
                                    ></has-error>
                                </div>

                                <div class="form-group">
                                    <input
                                        v-model="form.address"
                                        type="text"
                                        name="address"
                                        class="form-control"
                                        placeholder="Store Address"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'address'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="address"
                                    ></has-error>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button
                                    type="button"
                                    class="btn btn-danger"
                                    data-dismiss="modal"
                                >
                                    Close
                                </button>
                                <button
                                    v-show="editMode"
                                    type="submit"
                                    class="btn btn-success"
                                >
                                    Update
                                </button>
                                <button
                                    v-show="!editMode"
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Create
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    export default {
        created() {
            this.loadStores();
            this.getUser();
        },
        data() {
            return {
                is_busy: false,
                editMode: false,
                model: {},
                stores: {},
                store: "",
                user: "",
                storeUsers: "",
                reports: "",
                form: new Form({
                    id: "",
                    name: "",
                    address: "",
                }),
                stores:{
                    data: ''
                },
                filterForm: {
                    store_code: '',
                },
            };
        },

        methods: {
            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.user = data.user;
                });
            },

            getResults(page = 1) {
                axios.get('/api/store?page=' + page).then(response => {
                    this.stores = response.data;
                });
            },

            loadStores() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get('/api/store', { params: this.filterForm })

                .then(({ data }) => {
                    this.stores = data;
                    console.log(this.stores);
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadStores();
                this.getUser();
                this.$refs.filter.hide();
            },

            editModal(store) {
                (this.editMode = true), this.form.reset();
                $("#addNewstore").modal("show");
                this.form.fill(store);
            },

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNewstore").modal("show");
            },

            createstore() {
                axios.post("/api/store", this.form)
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Store Created Successfully.",
                        "success"
                    );
                    $("#addNewstore").modal("hide");
                    this.loadStores();
                })

                .catch(error => {
                    console.log(error.response.data.error);
                    if (error.response.data.error == "Store Already Exist") {
                        Swal.fire("Failed!", "Store Already Exist", "error");
                    }
                });
            },

            updatestore() {
                this.form.put("/api/store/" + this.form.id)
                .then(() => {
                    Swal.fire(
                        "Updated!",
                        "store Updated Successfully.",
                        "success"
                    );
                    $("#addNewstore").modal("hide");
                    this.loadStores();
                })

                .catch(() => {
                    this.$Progress.fail();
                });
            },

            onView(store) {
                this.$router.push({ path: "/store/" + store.code });
            },

            onProduct(store) {
                this.$router.push({ path: "/store/inventory/" + store.code });
            },

            onSale(store) {
                this.$router.push({ path: "/store/report/" + store.code });
            },

            deletestore(id) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                }).then(result => {
                    if (result.value) {
                        this.$Progress.start();
                        this.form
                            .delete("/api/store/" + id)
                            .then(() => {
                                Swal.fire(
                                    "Deleted!",
                                    "Store has been deleted.",
                                    "success"
                                );
                                this.$Progress.finish();
                                this.loadStores();
                            })

                            .catch(() => {
                                Swal(
                                    "Failed!",
                                    "Ops, Something went wrong, try again.",
                                    "Warning"
                                );
                            });
                    }
                });
            }
        }
    };
</script>
