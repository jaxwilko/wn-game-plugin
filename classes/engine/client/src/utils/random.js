export default class Random
{
    number(min, max) {
        return Math.floor(Math.random() * (max - min + 1) + min);
    }

    chance() {
        return Math.random() < 0.5;
    }

    chanceInt() {
        return this.chance() ? 1 : 0;
    }
}
