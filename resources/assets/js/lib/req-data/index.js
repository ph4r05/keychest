import _ from 'lodash';
import Req from 'req';
import ph4 from 'ph4';

export default {
    /**
     * processes groupBy result and returns [[key1, size1], [key2, size2], ...]
     * @param grouped
     * @param sort
     * @returns {Array}
     */
    groupStats(grouped, sort) {
        const agg = [];
        for (const [curLabel, val] of Object.entries(grouped)) {
            agg.push([curLabel, val.length]);  // zip
        }

        let sorted = agg;
        if (sort && (sort === 'label' || sort === 1)) {
            sorted = _.sortBy(sorted, x => {
                return x[0];
            });
        } else if (sort && (sort === 'count' || sort === 2)) {
            sorted = _.sortBy(sorted, x => {
                return x[1];
            });
        }

        return sorted;
    },

    /**
     * [g1 => [[l1,c1], [l2,c2]], ...]  - array of ziped datasets
     * after this function all datasets will have all keys, with defaul value 0 if it was not there before
     * @param groups
     * @returns {*}
     */
    mergeGroupStatsKeys(groups) {
        return this.mergeGroupKeys(groups, 0);
    },

    mergeGroupStatValues(groups) {
        // [g1 => [[l1,c1], [l2,c2]], ...]  - array of ziped datasets
        // returns a new single dataset [[l1, c1, c2], ...]

        // key array
        const keys = _.reduce(groups, (acc, x) => {
            return _.union(acc, _.unzip(x)[0]);
        }, []);

        const ret = [];
        const grpObjs = _.map(groups, _.fromPairs);

        _.forEach(keys, key => {
            const cur = [key];
            _.forEach(grpObjs, grp => {
                cur.push(key in grp ? grp[key] : 0);
            });
            ret.push(cur);
        });
        return ret;
    },

    /**
     * merges multiple datasets with zip-ed group structure, taking maximum count
     * @param groups
     * @returns {*}
     */
    mergeGroupStats(groups) {
        const x = _.reduce(groups, (result, value, key) => {
            const cur = _.fromPairs(value);
            return _.mergeWith(result, cur, (objValue, srcValue, key, object, source) => {
                return _.max([objValue, srcValue]);
            });
        }, {});

        return _.toPairs(x);
    },

    /**
     * [g1 => [[l1,obj1], [l2,obj2]], ...]
     * after this function all datasets will have all keys, with given default value if it was not there before
     * @param groups
     * @param missing
     */
    mergeGroupKeys(groups, missing) {
        const keys = {};
        _.forEach(groups, x => {
            _.assign(keys, Req.listToSet(_.unzip(x)[0]));
        });

        _.forEach(groups, x => {
            const curSet = Req.listToSet(_.unzip(x)[0]);
            _.forEach(keys, (val, key) => {
                if (!(key in curSet)) {
                    const curDefault = _.isFunction(missing) ?
                        missing(key, val, curSet, x) : missing;
                    x.push([key, curDefault]);
                }
            });
        });
    },

    /**
     * [g1 => [gg1=>obj, gg2=>obj, ...], g2 => ..., ...]
     * modifies the given groups so they have same labels and fills missing for missing pieces
     *
     * @param groups
     * @param missing
     */
    mergeGroups(groups, missing) {
        const keys = {};
        _.forEach(groups, x => {
            _.assign(keys, Req.listToSet(_.keys(x)));
        });

        _.forEach(groups, grp => {
            for (const curLabel in keys) {
                if (!(curLabel in grp)) {
                    grp[curLabel] = _.isFunction(missing) ? missing(curLabel, grp) : missing;
                }
            }
        });
    },

    /**
     * transforms [g1 => [gg1=>obj, gg2=>obj, ...], g2 => ..., ...]
     * to         [gg1=> [g1=>obj, g2=>obj,... ], gg2=> [] ]
     * returns a new object
     *
     * @param groups
     * @param missing
     */
    flipGroups(groups, missing) {
        const keys = {};
        _.forEach(groups, x => {
            _.assign(keys, Req.listToSet(_.keys(x)));
        });

        return _.reduce(keys, (acc, tmp, key) => {
            acc[key] =
                _.mapValues(groups, (gval, gkey) => {
                    return key in gval ? gval[key] : (_.isFunction(missing) ? missing(gval, groups) : missing);
                });

            return acc;
        }, {});
    },

    /**
     * [[[g1,c1], [g2, c2]], ...]
     * sorts each dataset separately based on the global ordering
     *
     * @param groups
     * @param fields
     * @param ordering
     * @returns {*}
     */
    mergedGroupStatSort(groups, fields, ordering) {
        // finds global ordering on the group keys by the count
        const mixed = this.mergeGroupStats(groups);

        // global ordering on the mixed dataset, get ranking for the keys
        const ordered = _.orderBy(mixed, fields, ordering);

        // ranking on the keys: key -> ranking
        const ranking = _.zipObject(
            _.unzip(ordered)[0],
            _.range(ordered.length));

        // sort by global ranking, in-place sorting. for
        _.forEach(groups, (grp, idx) => {  // grp is the dataset
            groups[idx].sort(Req.keyToCompare(y => {  // y is [g1, c1]
                return ranking[y[0]];
            }));

            // returns new object - does not touch original ones
            // groups[idx] = _.sortBy(grp, y => {  // y is [g1, c1]
            //     return ranking[y[0]];
            // });
        });
        return groups;
    }
}
