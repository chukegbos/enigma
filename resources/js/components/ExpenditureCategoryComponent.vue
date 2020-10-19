<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <b-button variant="outline-primary" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            <b-form-group label="Name:" label-for="keyword">
                                <b-form-input id="name" v-model="filterForm.name" type="text"></b-form-input>
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
                               Expenditure Category
                            </div>
                            <div class="card-tools">
                                <button class="btn btn-success" @click="newModal">
                                    Add Category
                                    <i class="fa fa-plus fa-fw"></i>
                                </button>
                            </div>
                        </div>
                        <div v-if="categories.data.length">
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <tr>
                                        <th>Name</th>
                                        <th>Modify</th>
                                    </tr>

                                    <tr
                                        v-for="category in categories.data"
                                        :key="category.id"
                                    >
                                        <td width="700px">{{ category.name }}</td>
                                        <td>

                                            <button class="btn btn-primary" @click.prevent="editModal(category)">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <button class="btn btn-danger" @click.prevent="deleteCategory(category.id)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="card-footer">
                                <pagination :data="categories" @pagination-change-page="getResults"></pagination>
                            </div>
                        </div>

                        <div v-else class="p-3">
                            <div class="alert alert-danger text-center">
                                No Category Found.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="modal fade"
                id="addNewcategory"
                tabindex="-1"
                role="dialog"
                aria-labelledby="addNewcategoryLabel"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5
                                class="modal-title"
                                id="addNewcategoryLabel"
                                v-show="!editMode"
                            >
                                Add New
                            </h5>
                            <h5
                                class="modal-title"
                                id="addNewcategoryLabel"
                                v-show="editMode"
                            >
                                Update category Info
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
                        <form @submit.prevent=" editMode ? updatecategory() : createcategory()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Category Name</label>
                                    <input v-model="form.name" type="text" class="form-control" :class="{'is-invalid': form.errors.has('name')}"/>
                                    <has-error :form="form" field="name"></has-error>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>
                                <button v-show="editMode" type="submit" class="btn btn-success">
                                    Update
                                </button>
                                <button v-show="!editMode" type="submit" class="btn btn-primary">
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
        this.getUser();
    },

    data() {
        return {
            is_busy: false,
            editMode: false,
            model: {},
            categories: [],
            user: "",
            form: new Form({
                id: "",
                name: "",
            }),
            categories: {
                data: '',
            },

            filterForm: {
                name: '',
            },
        };
    },

    created() {
        this.loadcategory();
        this.getUser();
    },

    methods: {
        getUser() {
            axios.get("/api/user")
            .then(({ data }) => {
                this.user = data.user;
            });
        },

        editModal(category) {
            (this.editMode = true), this.form.reset();
            $("#addNewcategory").modal("show");
            this.form.fill(category);
        },

        newModal() {
            (this.editMode = false), this.form.reset();
            $("#addNewcategory").modal("show");
        },

        onFilterSubmit()
        {
            this.loadcategory();
            this.getUser();
            this.$refs.filter.hide();
        },

        getResults(page = 1) {
            axios.get("/api/account/expenditure/category?page=" + page)
            .then(response => {
                this.categories = response.data;
            });
        },

        loadcategory() {
            if (this.is_busy) return;
            this.is_busy = true;
            axios.get("/api/account/expenditure/category", { params: this.filterForm })
            .then(({ data }) => {
                this.categories = data;
            })
            .finally(() => {
                this.is_busy = false;
            });
        },

        createcategory() {
            axios.post("/api/account/expenditure/category", this.form)
            .then(() => {
                Swal.fire(
                    "Created!",
                    "Category Created Successfully.",
                    "success"
                );
                $("#addNewcategory").modal("hide");
                this.loadcategory();
            })

            .catch(error => {
                console.log(error.response.data.error);
                if (
                    error.response.data.error == "Category Already Exist"
                ) {
                    Swal.fire(
                        "Failed!",
                        "Category Already Exist",
                        "error"
                    );
                }
            });
        },

        updatecategory() {
            this.form.put("/api/account/expenditure/category/" + this.form.id)
            .then(() => {
                Swal.fire(
                    "Updated!",
                    "Category Updated Successfully.",
                    "success"
                );
                $("#addNewcategory").modal("hide");
                this.loadcategory();
            })

            .catch(() => {
                this.$Progress.fail();
            });
        },

        deleteCategory(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            })
            .then(result => {
                if (result.value) {
                    this.$Progress.start();
                    this.form.delete("/api/account/expenditure/category/" + id)
                    .then(() => {
                        Swal.fire(
                            "Deleted!",
                            "Category has been deleted.",
                            "success"
                        );
                        this.$Progress.finish();
                        this.loadcategory();
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
    },
};
</script>
s