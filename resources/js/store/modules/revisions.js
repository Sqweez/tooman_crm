export default {
    state: {
        revisions: [],
        revision: {},
        revision_products: [],
    },
    getters: {
        revisions: s => s.revisions,
        revision: s => s.revision,
        revision_products: s => s.revision_products,
    },
    mutations: {
        setRevisions (s, p) {
            s.revisions = p;
        },
        updateRevision (s, p) {
            s.revisions = s.revisions.map(r => {
                if (r.id === p.id) {
                    r = {...p};
                }
                return r;
            })
        },
        deleteRevision (s, id) {
            s.revisions = s.revisions.filter(r => r.id !== id);
        },
        addRevision (s, r) {
            s.revisions.push(r);
        },
        setRevision (s, { revision, products }) {
            s.revision = revision;
            s.revision_products = products;
        }
    },
}
