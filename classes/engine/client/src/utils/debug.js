const options = {};

export default class Debug
{
    static get(key) {
        return options.enabled && (options[key] || null);
    }

    static set(key, value) {
        if (typeof key === "object") {
            Object.keys(key).forEach((k) => Debug.set(k, key[k]));
            return;
        }

        options[key] = value;
    }
}
