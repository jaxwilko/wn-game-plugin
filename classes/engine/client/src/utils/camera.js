import Vector from "./vector";

export default class Camera {
    constructor() {
        this.size = new Vector(0, 0);
        this.vector = new Vector(0, 0);
    }

    adjust(vector) {
        if (this.vector.x !== vector[0]) {
            this.vector.x = vector[0];
        }

        if (this.vector.y !== vector[1]) {
            this.vector.y = vector[1];
        }
    }
}
