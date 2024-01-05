export default class Vector
{
    constructor(x, y, z) {
        this.x = x;
        this.y = y;
        this.z = z;
    }

    toPoint() {
        return [this.x, this.y];
    }

    rotateY(theta) {
        return new Vector(
            Math.cos(theta) * this.x - Math.sin(theta) * this.z,
            this.y,
            Math.sin(theta) * this.x + Math.cos(theta) * this.z
        );
    }

    rotateX(theta) {
        return new Vector(
            this.x,
            Math.cos(theta) * this.y - Math.sin(theta) * this.z,
            Math.sin(theta) * this.y + Math.cos(theta) * this.z
        );
    }
}
