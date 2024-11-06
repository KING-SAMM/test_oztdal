<?php for ($i = 0; $i < 10; $i++): ?>
                    <fieldset>
                        <legend>Representative <?php echo $i + 1; ?></legend>
                        <label class="hide">Firstame:<br /> <input type="text" name="reps[<?php echo $i; ?>][firstname]" placeholder="First Name" id="firstname-<?php echo $i; ?>" required></label><br>
                        <span class="error firstname_err-<?php echo $i; ?>"></span><br>
                        <label class="hide">Lastame <br /> <input type="text" name="reps[<?php echo $i; ?>][lastname]" placeholder="Last Name" id="lastname-<?php echo $i; ?>" required></label><br>
                        <span class="error lastname_err-<?php echo $i; ?>"></span><br>
                        <label class="hide">Phone Number <br /> <input type="tel" name="reps[<?php echo $i; ?>][phone]" placeholder="Phone Number" id="phone-<?php echo $i; ?>" required></label><span class="error phone_err-<?php echo $i; ?>"></span><br>
                        <div class="profile_pic">
                            <label>Profile Picture: <input type="file" name="reps[<?php echo $i; ?>][profile_pic]" accept="image/jpeg, image/jpg, image/png, image/gif" onchange="previewImage(event, 'preview-<?php echo $i; ?>')" required></label>
                            <!-- Image preview for the selected profile picture -->
                            <img id="preview-<?php echo $i; ?>" class="preview" src="#" alt="Profile Picture Preview" style="display: none;">
                        </div>
                        <label>Gender:</label>
                        <input type="radio" name="reps[<?php echo $i; ?>][gender]" value="male" required> Male
                        <input type="radio" name="reps[<?php echo $i; ?>][gender]" value="female" required> Female
                    </fieldset>
                <?php endfor; ?>
